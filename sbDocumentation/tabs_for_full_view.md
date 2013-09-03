Localizing tabs for "full record" view
======================================

These are the default tabs for the full view of Solr and Summon records.
Please note that some tabs might be disabled in the config.ini file.

tabs in SolrMARC target
-----------------------

* HoldingsILS
* Description *(swissbib template)*
* TOC *(swissbib template)*
* UserComments *(disabled)*
* Reviews
* Excerpt
* HierarchyTree *(swissbib template)*
* Map
* Details => StaffViewMARC

tabs in Summon target
---------------------
* Description => articledetails  *(localized, swissbibmulti template)*
* TOC  *(disabled)*
* UserComments  *(disabled)*
* Reviews
* Excerpt
* Details => StaffViewArray


Localization
--------------

As far as possible, modules and templates should be shared between both
targets (Solr and Summon). When this is not feasible, we try to extend the
code with as little intrusion as possible. In this example, a separate
version of the "Details" tab has been implemented for Summon records.

**Configuration: Swissbib/config/module.config.php**

* in section **vufind->recorddriver_tabs**, override tab settings for different record drivers
    * set to *null* if you want to disable a tab
        * `'UserComments' => null`
    * set to a new identifier if you want to create a new plugin
        * `'Description' => 'articledetails'`
* in section **vufind->plugin_managers->recordtab->invokables** define our new plugin
    * `'articledetails' => 'Swissbib\RecordTab\ArticleDetails'`


**Module: Swissbib/RecordTab/ArticleDetails.php**

* Defines a new class extending the AbstractBase class for record tabs.
  Defining *getDescription()* is all it needs for an 'invokable' class.

**Template: themes/swissbibmulti/templates/RecordTab/articledetails.phtml**

* The nitty gritty detail of displaying a Summon record.


25.08.2013/andres.vonarx@unibas.ch

