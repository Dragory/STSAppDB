<h1>{{ __('generic.search_applications') }}</h1>
{{ View::make('__application_searchbar') }}

<h2>{{ __('generic.search_applications_results') }}</h2>
{{ View::make('__application_list', ['appList' => $appList, 'accounts' => $accounts, 'lang' => $lang]) }}