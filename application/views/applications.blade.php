<h1>{{ __('generic.applications_title') }}</h1>
<p>{{ __('generic.applications_description') }}</p>

<h2>{{ __('generic.search_applications') }}</h2>
{{ View::make('__application_searchbar') }}

<h2>{{ __('generic.latest_applications') }}</h2>
{{ View::make('__application_list', ['appList' => $appList, 'accounts' => $accounts, 'lang' => $lang]) }}