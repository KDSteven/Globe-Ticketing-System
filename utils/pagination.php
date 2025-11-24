<?php 
function build_pagination($page, $totalPages, $q, $status) {
    $html = '<div class="pagination">';

    $prev = max(1, $page - 1);
    $next = min($totalPages, $page + 1);

    // Build query string
    $qs = function($p) use ($q, $status) {
        $params = ['page' => $p];
        if ($q !== '') $params['q'] = $q;
        if ($status !== '') $params['status'] = $status;
        return '?' . http_build_query($params);
    };

    // First + Prev
    if ($page > 1) {
        $html .= '<a href="'.$qs(1).'">« First</a>';
        $html .= '<a href="'.$qs($prev).'">‹ Prev</a>';
    } else {
        $html .= '<span class="muted">« First</span>';
        $html .= '<span class="muted">‹ Prev</span>';
    }

    // Page window (current -2 to +2)
    $start = max(1, $page - 2);
    $end   = min($totalPages, $page + 2);
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $page) {
            $html .= '<span class="active">'.$i.'</span>';
        } else {
            $html .= '<a href="'.$qs($i).'">'.$i.'</a>';
        }
    }

    // Next + Last
    if ($page < $totalPages) {
        $html .= '<a href="'.$qs($next).'">Next ›</a>';
        $html .= '<a href="'.$qs($totalPages).'">Last »</a>';
    } else {
        $html .= '<span class="muted">Next ›</span>';
        $html .= '<span class="muted">Last »</span>';
    }

    $html .= '</div>';
    return $html;
}
?>