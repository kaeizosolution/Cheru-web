<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends MY_Model {

    public function get_category_tree($parent_id = 0)
    {
        $this->slave->where('parent_id', $parent_id);
        $this->slave->order_by('name', 'ASC');
        $query = $this->slave->get('ec_categories_prod');
        $categories = $query->result();

        $tree = [];

        foreach ($categories as $category) {
			if ($this->has_products($category->id)) {
            $children = $this->get_category_tree($category->id);
            $node = [
                'id'       => $category->id,
                'name'     => $category->name,
                'slug'     => $category->slug,
                'children' => $children
            ];
            $tree[] = $node;
        }
		}
        return $tree;
    }

	private function has_products($category_id)
{
    $this->slave->group_start();
    $this->slave->where('category_id', $category_id);
    $this->slave->or_where('sub_cat_id', $category_id);
    $this->slave->group_end();

    $this->slave->where('status', '1');
    $query = $this->slave->get('products');

    if ($query->num_rows() > 0) {
        return true;
    }

    $this->slave->where('parent_id', $category_id);
    $child_cats = $this->slave->get('ec_categories_prod')->result();

    foreach ($child_cats as $child) {
        if ($this->has_products($child->id)) {
            return true;
        }
    }

    return false;
}

		
	
}

