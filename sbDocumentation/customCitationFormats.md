# Customizing Citation Formats

This documentation is a step by step guide to add custom citation formats.
In the source code are already templates named *custom* that include dummy code.

1. module/Swissbib/src/Swissbib/VuFind/View/Helper/Root/Citation.php  
   In this file a method must implemented with the name getCitation[ **your custom citation name** ].   There is a template called *getCitationCustom*.  
   The name of the method must match the name in the config_base.ini.  
   The function must return a version for books (Citation/custom.phtml) and a version for journals (Citation/custom-article.phtml).  
   You can add as much information as you want to the $custom array and pass it to the template.  
2. Both templates must be added to the corresponding theme.  
   *i.e. themes/swissbib/templates/Citation/custom.phtml*  
   *i.e. themes/swissbib/templates/Citation/custom-article.phtml*  
   The appearance of the citation is fully handled in the template. For further help look here: themes/root/templates/Citation  
3. The label key for the citation gets built by the name of your citation, a space and Citation.  
   *i.e. APA Citation or Custom Citation*  
   The label has to be added to the language files.  
4. You have to add your customized citation to the config_base.ini in the **record** section.  
   *i.e. citation_formats = APA,MLA,Custom*