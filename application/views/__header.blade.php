<div id="menu">
<?php if ($user): ?>
    <ul>
        <li><a href="{{ URL::to_route('index') }}">Dashboard</a></li>
        <li><a href="{{ URL::to_route('applications') }}">Applications</a></li>
        <li><a href="{{ URL::to_route('addApplication') }}">Add a new application</a></li>
        <li>
            <form id="form-lang" action="{{ URL::to_route('changeLanguage') }}" method="post">
                {{ Form::token() }}
                <select id="form-lang-select" name="language">
<?php
    foreach ($user->lang_access as $language)
    {
        echo '<option val="'.$language.'"'.($language == $lang->lang_name_safe ? ' selected="selected"' : '').'>'.ucfirst($language).'</option>';
    }
?>

                </select>
            </form>
        </li>
    </ul>
<?php else: ?>
    <p>Please log in</p>
<?php endif; ?>
</div>
<div id="userbar">
<?php if ($user): ?>
    {{ $user->account->acc_name }}
<?php endif; ?>
</div>