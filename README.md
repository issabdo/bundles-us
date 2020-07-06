Installation
============

I - USAGE : 
 ---------
 
 * References bundle in AppKernel.php => ... new Us\Bundle\SecurityBundle\UsSecurityBundle()
 
 
 * References bundle config in config.yml:
    ex.
    ...
    imports:  
        ...
        - { resource: "@UsSecurityBundle/Resources/config/services.yml" }
    
      
 * Defines configuration in global config.yml:
    ex.
    ...
    us_security:
        document_validation_handler: 'us.api.document.validation.handler'
        authentication:
         partners_token:
             TEST: "app.mobile"