<?php
    if (isset($_GET['term'])) $term = htmlentities($_GET['term'], ENT_QUOTES, 'UTF-8');
    else $term = '';
?>
<form action="{{ URL::to_route('applicationSearch') }}" method="get">
    <input type="text" name="term" value="{{ $term }}"> <input type="submit" value="{{ __('generic.search_go') }}">
</form>