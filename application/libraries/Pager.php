<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pager 
{
    private $class_pagination = 'pagination';
    private $class_default = 'page';
    private $class_disabled = 'disabled';
    private $class_current = 'active';

    function setStyles($pagination = '', $default = '', $disabled = '', $current = '')
    {
        if ($pagination != '') {
            $this->class_pagination = $pagination;
        }
        if ($default != '') {
            $this->class_default = $default;
        }
        if ($disabled != '') {
            $this->class_disabled = $disabled;
        }
        if ($current != '') {
            $this->class_current = $current;
        }
    }

    function showLinks($url, $current_page, $total_records, $rpp = 10)
    {
        $adjacents = 2;
        $class_pagination = $this->class_pagination;
        $page = $this->class_default;
        $current = $this->class_current;
        $disabled = $this->class_disabled;
        $pagination = "";
        $prev = $current_page - 1; // previous page is page - 1
        $next = $current_page + 1; // next page is page + 1
        $lastpage = ceil($total_records / $rpp); // lastpage is = total pages / items per page, rounded up.
        $lpm1 = $lastpage - 1; // last page minus 1
        $first = 1; // first page
        $second = 2; // second page
        #$prev = $url . '' . $prev;
        if ($lastpage > 1) {
            $pagination .= "<div class=\"col-md-12 customPagination\"><ul class=\"pagination justify-content-center\" style=\"margin:20px 0\">";
            // previous button
            if ($current_page > 1){
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:void(0);\" onclick=get_data(\"$prev\");>Prev</a></li>";
            }else{
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:void(0);\" style=\"cursor: not-allowed;\"> Prev</a></li>";
            }
// pages
            if($lastpage < 7 + ($adjacents * 2)){ // not enough pages to bother breaking it up
                for ($counter = 1; $counter <= $lastpage; $counter ++) {
                    $i = $url . '' . $counter;
                    if ($counter == $current_page){
                        $pagination .= "<li class=\"page-item $current\"><a class=\"page-link\" href=\"javascript:void(0);\" style=\"cursor: context-menu;\">$counter</a></li>";
                    }else{
                        $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:void(0);\" onclick=get_data(\"$counter\"); class=\"$page\">$counter</a></li>";
                    }
                }
            } elseif ($lastpage > 5 + ($adjacents * 2)){// enough pages to hide some // close to beginning; only hide later pages
                if ($current_page < 1 + ($adjacents * 2)) {
                    for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter ++) {
                        $i = $url . '' . $counter;
                        if ($counter == $current_page)
                            $pagination .= "<li class=\"page-item $current\"><a class=\"page-link\" href=\"javascript:void(0);\" style=\"cursor: context-menu;\">$counter</a></li>";
                        else
                            $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:void(0);\" onclick=get_data(\"$counter\"); class=\"$page\">$counter</a></li>";
                    }
                    $lpm11 = $url . '' . $lpm1;
                    $lastpage1 = $url . '' . $lastpage;
                    $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript: void(0)\" style=\"cursor: context-menu;\">...</a></li>";
                    $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:void(0);\" onclick=get_data(\"$lpm11\"); class=\"$page\">$lpm1</a></li>";
                    $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:void(0);\" onclick=get_data(\"$lastpage\"); class=\"$page\">$lastpage</a></li>";
                } elseif ($lastpage - ($adjacents * 2) > $current_page && $current_page > ($adjacents * 2)) { // in middle; hide some front and some back
                    $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:void(0);\" onclick=get_data(\"$first\"); class=\"$page\">1</a></li>";
                    $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:void(0);\" onclick=get_data(\"$second\"); class=\"$page\">2</a></li>";
                    $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript: void(0)\" style=\"cursor: context-menu;\">...</a></li>";
                    for ($counter = $current_page - $adjacents; $counter <= $current_page + $adjacents; $counter ++) {
                        $i = $url . '' . $counter;
                        if ($counter == $current_page)
                            $pagination .= "<li class=\"page-item $current\"><a class=\"page-link\" href=\"javascript:void(0);\" style=\"cursor: context-menu;\">$counter</a></li>";
                        else
                            $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:void(0);\" onclick=get_data(\"$counter\"); class=\"$page\">$counter</a></li>";
                    }
                    $lpm11 = $url . '' . $lpm1;
                    $lastpage1 = $url . '' . $lastpage;
                    $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript: void(0)\" style=\"cursor: context-menu;\">...</a></li>";
                    $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:void(0);\" onclick=get_data(\"$lpm11\"); class=\"$page\">$lpm1</a></li>";
                    $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:void(0);\" onclick=get_data(\"$lastpage1\"); class=\"$page\">$lastpage</a></li>";
                }else {
                    $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript: void(0)\" style=\"cursor: context-menu;\">...</a></li>";
                    for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter ++) {
                        $i = $url . '' . $counter;
                        if ($counter == $current_page)
                            $pagination .= "<li class=\"page-item $current\"><a class=\"page-link\" href=\"javascript: void(0)\" style=\"cursor: context-menu;\">$counter</a></li>";
                        else
                            $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:void(0);\" onclick=get_data(\"$counter\"); class=\"$page\">$counter</a></li>";
                    }
                }
            }
// next button
            if ($current_page < $counter - 1) {
                #$next = $url . '' . $next;
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:void(0);\" onclick=get_data(\"$next\"); class=\"$page\">Next </a></li>";
            }else{
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"javascript:void(0);\" style=\"cursor: not-allowed;\" class=\"$page\">Next </a></li>";
            }
            $pagination .= "</ul></div>\n";
        }
        return $pagination;
    }
}
