#Installation

Marketplace Advanced Booking And Reservation For Magento2 module installation is very easy, please follow the steps for installation-

1. Unzip the respective extension zip and create Webkul(vendor) and MpAdvancedBookingSystem(module) name folder inside your magento/app/code/ directory and then move all module's files into magento root directory Magento2/app/code/Webkul/MpAdvancedBookingSystem/ folder.

Run Following Command via terminal
-----------------------------------
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

now module is properly installed

#Uninstallation

Note: After uninstallation, all data of the module will be deleted from the instance. It will completely uninstall the module.

Please follow the steps for uninstallation-

1.  Run Following Command via terminal
    -----------------------------------
    php bin/magento mpadvancedbookingsystem:disable
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

#Reinstallation

If this module has been uninstalled by using the upper #Uninstallation process then Please follow the steps for reinstallation-

1.  Run Following Command via terminal
    -----------------------------------
    php bin/magento module:enable Webkul_MpAdvancedBookingSystem
    php bin/magento setup:upgrade
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

#User Guide

For Marketplace Advanced Booking And Reservation For Magento2 module's working process follow user guide - https://webkul.com/blog/magento2-multi-vendor-rental-event-appointment-hotel-booking/

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/
