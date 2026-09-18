<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rating extends MY_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('Query_model');
        $this->TYPE     = $this->session->userdata('type') ?: 'customer';
        $sess           = $this->session->userdata($this->TYPE);
        $this->LOGIN_ID = ($sess && isset($sess['login_id'])) ? (int)$sess['login_id'] : 0;
    }

    // ── Legacy action-based entry (keep for back-compat) ─────────────────────
    public function index()
    {
        if (is_api()) {
            $method = $this->input->post('action');
            if ($method && method_exists($this, $method)) {
                $response = $this->$method();
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response));
                return;
            }
        }
        // Fallback — nothing to render directly
        redirect('/');
    }

    // ── STEP 2A: Submit / update a review ───────────────────────────────────
    // POST: product_id, rating, title (opt), review (opt)
    public function submit()
    {
        header('Content-Type: application/json');

        // Must be logged in
        if (!$this->LOGIN_ID) {
            echo json_encode(['status' => 0, 'msg' => 'Please login to submit a review.']);
            return;
        }

        $product_id = (int)$this->input->post('product_id');
        $rating     = (int)$this->input->post('rating');
        $title      = trim((string)$this->input->post('title'));
        $review     = trim((string)$this->input->post('review'));

        if (!$product_id) {
            echo json_encode(['status' => 0, 'msg' => 'Invalid product.']);
            return;
        }
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['status' => 0, 'msg' => 'Please select a star rating (1–5).']);
            return;
        }

        $post_data = [
            'customer_id' => $this->LOGIN_ID,
            'product_id'  => $product_id,
            'title'       => $title,
            'review'      => $review,
            'rating'      => $rating,
            'status'      => '0',  // pending admin approval
        ];

        // Check if this customer already reviewed this product
        $existing = $this->db
            ->get_where('ec_review_rating', [
                'product_id'  => $product_id,
                'customer_id' => $this->LOGIN_ID,
            ])->row();

        if ($existing) {
            $this->db
                ->where('product_id',  $product_id)
                ->where('customer_id', $this->LOGIN_ID)
                ->update('ec_review_rating', $post_data);
            echo json_encode(['status' => 1, 'msg' => 'Your review has been updated and is pending admin approval.']);
        } else {
            $this->db->insert('ec_review_rating', $post_data);
            echo json_encode(['status' => 1, 'msg' => 'Thank you! Your review has been submitted and is pending admin approval.']);
        }
    }

    // ── STEP 2B: Fetch reviews for a product ────────────────────────────────
    // GET: /customer/rating/product_reviews/{product_id}
    public function product_reviews($product_id = 0)
    {
        header('Content-Type: application/json');
        $product_id = (int)$product_id;
        if (!$product_id) {
            echo json_encode(['status' => 0, 'msg' => 'Invalid product.', 'data' => []]);
            return;
        }

        // Fetch approved reviews OR the current customer's pending review
        $login_id = (int)$this->LOGIN_ID;
        $this->db
            ->select('r.review_rating_id, r.customer_id, r.title, r.rating, r.review, r.status, r.date_created')
            ->select("CONCAT(COALESCE(c.fname,''), ' ', COALESCE(c.lname,'')) AS reviewer_name", false)
            ->from('ec_review_rating r')
            ->join('ec_customer c', 'c.customer_id = r.customer_id', 'left')
            ->where('r.product_id',  $product_id);
            
        if ($login_id > 0) {
            $this->db->where("(r.status = '1' OR r.customer_id = $login_id)");
        } else {
            $this->db->where('r.status', '1');
        }
        
        $reviews_raw = $this->db
            ->order_by('r.date_created', 'DESC')
            ->get()->result_array();

        // Calculate breakdown/average using ONLY approved reviews
        $total = 0;
        $sum = 0;
        $star_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        
        $my_review = null;
        
        foreach ($reviews_raw as &$r) {
            $s = (int)$r['rating'];
            
            // Is it current customer's review?
            if ($login_id > 0 && (int)$r['customer_id'] === $login_id) {
                $my_review = $r;
            }
            
            if ($r['status'] === '1') {
                $total++;
                if (isset($star_counts[$s])) $star_counts[$s]++;
                $sum += $s;
            }

            // Clean reviewer name
            $name = trim($r['reviewer_name']);
            $r['reviewer_name']   = $name ?: 'Anonymous';
            $r['reviewer_initial'] = strtoupper(substr($r['reviewer_name'], 0, 1));
            $r['date_formatted']  = date('F j, Y', strtotime($r['date_created']));
            $r['rating']          = (int)$r['rating'];
        }
        unset($r);

        $avg = $total > 0 ? round($sum / $total, 1) : 0;

        // Per-star percentage
        $breakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $cnt = $star_counts[$i];
            $breakdown[$i] = [
                'count'   => $cnt,
                'percent' => $total > 0 ? round(($cnt / $total) * 100) : 0,
            ];
        }

        echo json_encode([
            'status' => 1,
            'data'   => [
                'reviews'   => $reviews_raw,
                'summary'   => [
                    'total'     => $total,
                    'avg'       => $avg,
                    'breakdown' => $breakdown,
                ],
                'my_review' => $my_review,
            ],
        ]);
    }

    // ── Legacy rating_list (keep for back-compat) ───────────────────────────
    public function rating_list()
    {
        $review_rating_arr = $this->Query_model->get_data('ec_review_rating', ['customer_id' => $this->LOGIN_ID]);
        if (count($review_rating_arr))
            api_response(['status' => 1, 'msg' => 'Success', 'data' => $review_rating_arr]);
        else
            api_response(['status' => 0, 'msg' => 'There is no data.', 'data' => []]);
    }

    // ── Legacy add_review (keep for back-compat) ────────────────────────────
    private function add_review()
    {
        $customer_id = $this->LOGIN_ID;
        $this->load->library('form_validation');
        $this->form_validation->set_rules('product_id', 'Product id', 'trim|required');
        $this->form_validation->set_rules('rating',     'Rating',     'trim|required');

        if (!$customer_id || $this->form_validation->run() == FALSE) {
            return ['status' => '0', 'message' => !$customer_id ? 'Please login first.' : validation_errors(), 'data' => []];
        }

        $post_data = [
            'customer_id' => $customer_id,
            'product_id'  => $this->input->post('product_id'),
            'review'      => $this->input->post('review'),
            'rating'      => $this->input->post('rating'),
        ];

        $existing = $this->Query_model->get_data_obj('ec_review_rating', [
            'product_id'  => $post_data['product_id'],
            'customer_id' => $customer_id,
        ]);

        if ($existing) {
            $this->Query_model->update_data('ec_review_rating', $post_data, [
                'customer_id' => $customer_id,
                'product_id'  => $post_data['product_id'],
            ]);
            return ['status' => '1', 'message' => 'Review and rating successfully updated.', 'data' => []];
        }

        $this->Query_model->insert_data('ec_review_rating', $post_data);
        return ['status' => '1', 'message' => 'Review and rating successfully added.', 'data' => []];
    }
}
