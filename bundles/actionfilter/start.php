<?php

Autoloader::namespaces(array(
	'ActionFilter' => Bundle::path('actionfilter').'src',
));

Autoloader::map(array(
	'ActionFilter\\Filter_Controller' => Bundle::path('actionfilter').'controllers/filter.php',
));
