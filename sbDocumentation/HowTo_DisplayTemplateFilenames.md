## How to Display Template Filenames during Development

During development, it is possible to include hidden comments
into the source code of HTML/XML pages which show the beginning and
the end of all the templates used to generate the HTML/XML page.

## How it works

* This feature can be turned on by a switch in local/httpd-vufind.conf :

    * SetEnv VUFIND_ENV development

* public/index.php:

    * Depending on the setting of the environment variable VUFIND_ENV,
    the global variable APPLICATION_ENV is set to "production" or to
    "development".

* module/Swissbib/src/Swissbib/Bootstrapper.php:

    * If APPLICATION_ENV is set to "development", the class TemplateFilenameFilter
    is appended to the filter chain.

* module/Swissbib/src/Swissbib/Filter/TemplateFilenameFilter.php:

    * This filter does only work on content marked as &lt;html&gt; or &lt;xml&gt;.
    * This filter does *not* apply to files used for export download or user email.
    * Content is wrapped between comments which contain the full path of the template:
    &lt;!-- Begin: ... --&gt; and &lt;!-- End: ... --&gt;.

