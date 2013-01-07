<table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th>{{ __('generic.table_header_applicant') }}</th>
            <th>{{ __('generic.table_header_token') }}</th>
            <th>{{ __('generic.table_header_verdict') }}</th>
            <th>{{ __('generic.table_header_moderator') }}</th>
            <th>{{ __('generic.table_header_date') }}</th>
            <th>{{ __('generic.table_header_details') }}</th>
        </tr>
    </thead>
    <tbody>
<?php
    foreach ($appList as $app)
    {
        $moderator = $accounts[$app->app_moderator_steam64];
        $applicant = $accounts[$app->app_applicant_steam64];

        if ($app->app_verdict == 1) $verdict = '<span class="verdict-accepted">Accepted</span>';
        else $verdict = '<span class="verdict-declined">Declined</span>';

        $tokenLink = sprintf('http://translation.steampowered.com/translate.php?search_input=%s&lang=%s', $app->app_token, $lang->lang_name_safe);

        echo '<tr>'.
                '<td>'.
                    '<img src="'.$applicant->acc_avatar.'" alt="'.htmlentities($applicant->acc_name, ENT_QUOTES, 'UTF-8').'\'s avatar">'.
                    $applicant->acc_name.
                '</td>'.
                '<td><a href="'.$tokenLink.'">'.$app->app_token.'</a></td>'.
                '<td>'.$verdict.'</td>'.
                '<td>'.
                    '<img src="'.$moderator->acc_avatar.'" alt="'.htmlentities($moderator->acc_name, ENT_QUOTES, 'UTF-8').'\'s avatar">'.
                    $moderator->acc_name.'</td>'.
                '<td>'.date('d.m.Y', strtotime($app->app_time)).'</td>'.
                '<td><a href="'.URL::to_route('application', array($app->id_application)).'">'.__('generic.table_view_details').'</a></td>'.
             '</tr>';
    }
?>
    </tbody>
</table>