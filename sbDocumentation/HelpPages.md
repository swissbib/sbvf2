# Help Pages
The help pages for swissbib contain static HTML content. They are rendered as normal templates and support all expected features.

## Route
The help pages can be accessed under the route /HelpPage for the main page (search) or via /HelpPage/Search to address
a topic directly.
Use the 'help-page' route to link to a page:

	<?=$this->url('help-page')?>
	// Result: /HelpPage

	<?=$this->url('help-page', array('topic'=>'search'))?>
	// Result: /HelpPage/search

## Config
The available help pages are defined in under the main config in the section [HelpPages].
The config is located at */local/config/vufind/config_base.ini*

	[HelpPages]
	pages[] = search
	pages[] = save
	pages[] = faq
	pages[] = data
	pages[] = searchbox

## Files and Translation
The templates are located at /themes/swissbib/templates/HelpPage and are separated in a locale specific folder.
The default language is english, so if a help page is not translated for the selected language, it will display
the english version. Make sure the english version exists. The file names have to correspond with the configuration.
The file layout.phtml wraps the content files and displays the navigation.

## Images
To include images, use the basePath() view helper.

	<img src="<?= $this->basePath('themes/swissbib/css/themes/orange/img/icon_info.gif') ?>">
Will result in:

	<img src="/vufind/themes/swissbib/css/themes/orange/img/icon_info.gif">
The page will be absolute, so it may be different depending how the application is reachable on the webserver.
