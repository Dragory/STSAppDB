<?php

class Applications
{
    public function getApplicationsForLanguage($lang_name_safe)
    {
        return DB::table('applications')
            ->where('lang_name_safe', '=', $lang_name_safe)
            ->get();
    }

    public function searchApplications($apps, $accounts, $term)
    {
        $results = [];
        foreach ($apps as $app)
        {
            $applicant = $accounts[$app->app_applicant_steam64];
            $moderator = $accounts[$app->app_moderator_steam64];

            if (
                stripos($applicant->acc_name, $term) !== false
                || stripos($moderator->acc_name, $term) !== false
            ) {
                $results[] = $app;
            }
        }

        return $results;
    }
}