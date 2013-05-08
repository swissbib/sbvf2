# Tab Templates

Use the view helper tabTemplate() to search for a custom tab template.
The helper looks for a template with the following path

Input path

	search/results/list (=> themes/THEMENAME/search/results/list.phtml)

Excepted result if custom template for summon is present

	search/results/list.summon (=> themes/THEMENAME/search/results/list.summon.phtml)


If there is no custom template or tabkey, the given template path is returned.

Example

	<?php

	$this->render($this->tabTemplate('search/results', $tabKey));

	?>

