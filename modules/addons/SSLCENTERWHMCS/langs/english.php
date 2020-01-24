<?php

$_LANG['token']        = ', Error Token:';
$_LANG['generalError'] = 'Something has gone wrong. Check logs and contact admin';

//SSLCENTER configuration
$_LANG['addonAA']['pagesLabels']['label']['apiConfiguration']                                          = 'Configuration';
$_LANG['addonAA']['apiConfiguration']['crons']['header']                                               = 'Crons';
//synchronization cron
$_LANG['addonAA']['apiConfiguration']['DailyCron']['pleaseNote']                             = 'Please Note:';
$_LANG['addonAA']['apiConfiguration']['DailyCron']['info']                                   = 'In order to enable automatic daily synchronization, please set up a following cron command line:';
$_LANG['addonAA']['apiConfiguration']['DailyCron']['commandLine']['cronFrequency']           = '0 0 * * *';
//processing cron
$_LANG['addonAA']['apiConfiguration']['cronProcessing']['pleaseNote']                             = 'Please Note:';
$_LANG['addonAA']['apiConfiguration']['cronProcessing']['info']                                   = 'In order to enable automatic synchronization of processing orders every 5th minutes, please set up a following cron command line :';
$_LANG['addonAA']['apiConfiguration']['cronProcessing']['commandLine']['cronFrequency']           = '*/5 * * * *';

//synchronization cron
$_LANG['addonAA']['apiConfiguration']['cronSynchronization']['pleaseNote']                             = 'Please Note:';
$_LANG['addonAA']['apiConfiguration']['cronSynchronization']['info']                                   = 'In order to enable automatic synchronization, please set up a following cron command line (every hour suggested):';
$_LANG['addonAA']['apiConfiguration']['cronSynchronization']['commandLine']['cronFrequency']           = '0 */1 * * *';
//summary order cron
$_LANG['addonAA']['apiConfiguration']['cronSSLSummaryStats']['pleaseNote']                             = 'Please Note:';
$_LANG['addonAA']['apiConfiguration']['cronSSLSummaryStats']['info']                                   = 'In order to enable load current SSL orders status, please set up a following cron command line (every 4 hours suggested):';
$_LANG['addonAA']['apiConfiguration']['cronSSLSummaryStats']['commandLine']['cronFrequency']           = '1 */4 * * *';
//customers notification and creating renewals
$_LANG['addonAA']['apiConfiguration']['cronRenewal']['pleaseNote']                                     = 'Please Note:';
$_LANG['addonAA']['apiConfiguration']['cronRenewal']['info']                                           = 'In order to send customers notifications of expiring services and create renewal invoices for services that expire within the selected number of days, set the following command line cron (once a day suggested):';
$_LANG['addonAA']['apiConfiguration']['cronRenewal']['commandLine']['cronFrequency']                   = '0 0 * * *';
//customers send certificate
$_LANG['addonAA']['apiConfiguration']['cronSendCertificate']['pleaseNote']                             = 'Please Note:';
$_LANG['addonAA']['apiConfiguration']['cronSendCertificate']['info']                                   = 'In order to send a certificate to the client when the SSL order changes to active status, set the following command line cron (every 3 hours suggested):';
$_LANG['addonAA']['apiConfiguration']['cronSendCertificate']['commandLine']['cronFrequency']           = '0 3 * * *';
//customers send certificate
$_LANG['addonAA']['apiConfiguration']['cronPriceUpdater']['pleaseNote']                             = 'Please Note:';
$_LANG['addonAA']['apiConfiguration']['cronPriceUpdater']['info']                                   = 'In order to synchronize the WHMCS product prices with the API product prices, set the following command line cron (every 3rd day suggested):';
$_LANG['addonAA']['apiConfiguration']['cronPriceUpdater']['commandLine']['cronFrequency']           = '0 0 */3 * *';
//customers send certificate
$_LANG['addonAA']['apiConfiguration']['cronCertificateDetailsUpdater']['pleaseNote']                             = 'Please Note:';
$_LANG['addonAA']['apiConfiguration']['cronCertificateDetailsUpdater']['info']                                   = 'In order to synchronize certificate details in WHMCS with the certificate details in API, set the following command line cron (once a day suggested):';
$_LANG['addonAA']['apiConfiguration']['cronCertificateDetailsUpdater']['commandLine']['cronFrequency']           = '0 0 * * *';
//
$_LANG['addonAA']['apiConfiguration']['item']['header']                                                = 'API Configuration';
$_LANG['addonAA']['apiConfiguration']['item']['api_login']['label']                                    = 'Login';
$_LANG['addonAA']['apiConfiguration']['item']['api_password']['label']                                 = 'Password';
$_LANG['addonAA']['apiConfiguration']['item']['tech_legend']['label']                                  = 'Technical Contact';
$_LANG['addonAA']['apiConfiguration']['item']['csr_generator_legend']['label']                         = 'CSR Generator';
$_LANG['addonAA']['apiConfiguration']['item']['display_csr_generator']['label']                        = 'Allow To Use CSR Generator';
$_LANG['addonAA']['apiConfiguration']['item']['default_csr_generator_country']['description']          = 'The default selection';
$_LANG['addonAA']['apiConfiguration']['item']['display_ca_summary']['label']                           = 'Display Orders Summary';
$_LANG['addonAA']['apiConfiguration']['item']['client_area_summary_orders']['label']                   = 'Client Area Orders Summary';
$_LANG['addonAA']['apiConfiguration']['item']['validation_settings']['label']                          = 'Validation Settings';
$_LANG['addonAA']['apiConfiguration']['item']['disable_email_validation']['label']                     = 'Disable Email Validation';
$_LANG['addonAA']['apiConfiguration']['item']['summary_expires_soon_days']['label']                    = 'Expires Soon';
$_LANG['addonAA']['apiConfiguration']['item']['summary_expires_soon_days']['description']              = 'Count SSL order for statistics, if there are fewer or equal days to expire than the selected ones.';
$_LANG['addonAA']['apiConfiguration']['item']['send_certificate_template']['label']                    = 'Send Certificate Email Template';
$_LANG['addonAA']['apiConfiguration']['item']['send_certificate_template']['description']              = 'To send an SSL certificate through the chosen template, edit it and place the {$ssl_certyficate} field in it.';
//
$_LANG['addonAA']['apiConfiguration']['item']['data_migration_legend']['label']                        = 'Data & Configuration Migration';
$_LANG['addonAA']['apiConfiguration']['item']['data_migration']['content']                             = 'Migrate';
$_LANG['addonAA']['apiConfiguration']['modal']['import']                                               = 'Migrate';
$_LANG['addonAA']['apiConfiguration']['modal']['close']                                                = 'Close';
$_LANG['addonAA']['apiConfiguration']['modal']['migrationData']                                        = 'Import Data & Configuration';
$_LANG['addonAA']['apiConfiguration']['migrationOldModuleDataExixts']                                  = 'There are products or services associated with the GGSSL WHMCS module:';
$_LANG['addonAA']['apiConfiguration']['migrationProductIDs']                                           = 'Product IDs: ';
$_LANG['addonAA']['apiConfiguration']['migrationServiceIDs']                                           = 'Service IDs: ';
$_LANG['addonAA']['apiConfiguration']['migrationPerformMigration']                                     = 'Perform data migration to associate configuration and data with the SSLCENTER WHMCS module.';
$_LANG['addonAA']['apiConfiguration']['modal']['dataMigrationInfo']                                    = 'You are about to migrate data and configuration from GGSSL WHMCS module, this procedure is irreversible.';
$_LANG['addonAA']['apiConfiguration']['modal']['dataMigrationInfo2']                                   = 'Activities that will be performed:';
$_LANG['addonAA']['apiConfiguration']['modal']['dataMigrationInfoAction'][0]                           = 'import of the addon configuration';
$_LANG['addonAA']['apiConfiguration']['modal']['dataMigrationInfoAction'][1]                           = 'update of existing products (change of assigned module)';
$_LANG['addonAA']['apiConfiguration']['modal']['dataMigrationInfoAction'][2]                           = 'update of existing services (change of assigned module)';
$_LANG['addonAA']['apiConfiguration']['messages']['data_migration_success']                            = 'Data and configuration have been imported successfully. The page will automatically reloaded after 5 seconds.';
//
$_LANG['addonAA']['apiConfiguration']['item']['renewal_settings_legend']['label']                      = 'Renewal Settings';
$_LANG['addonAA']['apiConfiguration']['item']['logs_settings_legend']['label']                      = 'Logs Settings';
$_LANG['addonAA']['apiConfiguration']['item']['auto_renew_invoice_reccuring']['label']                 = 'Recuring Orders';
$_LANG['addonAA']['apiConfiguration']['item']['auto_renew_invoice_reccuring']['description']           = 'Create automatical renewal invoice';
$_LANG['addonAA']['apiConfiguration']['item']['send_expiration_notification_reccuring']['label']       = '';
$_LANG['addonAA']['apiConfiguration']['item']['renew_invoice_days_reccuring']['description']           = 'Days before expiry';
$_LANG['addonAA']['apiConfiguration']['item']['send_expiration_notification_reccuring']['description'] = 'Send expiration notifications';
$_LANG['addonAA']['apiConfiguration']['item']['auto_renew_invoice_one_time']['label']                  = 'One Time Orders';
$_LANG['addonAA']['apiConfiguration']['item']['auto_renew_invoice_one_time']['description']            = 'Create automatical renewal invoice';
$_LANG['addonAA']['apiConfiguration']['item']['send_expiration_notification_one_time']['label']        = '';
$_LANG['addonAA']['apiConfiguration']['item']['renew_invoice_days_one_time']['description']            = 'Days before expiry';
$_LANG['addonAA']['apiConfiguration']['item']['send_expiration_notification_one_time']['description']  = 'Send expiration notifications';

$_LANG['addonAA']['apiConfiguration']['item']['automatic_processing_of_renewal_orders']['label']                  = '';
$_LANG['addonAA']['apiConfiguration']['item']['automatic_processing_of_renewal_orders']['description']            = 'Automatic processing of renewal orders';
$_LANG['addonAA']['apiConfiguration']['item']['renew_new_order']['label']                  = '';
$_LANG['addonAA']['apiConfiguration']['item']['renew_new_order']['description']            = 'Renew order via existing order';
$_LANG['addonAA']['apiConfiguration']['item']['visible_renew_button']['label']                  = '';
$_LANG['addonAA']['apiConfiguration']['item']['visible_renew_button']['description']            = 'Visible "Renew" button in ClientArea';
$_LANG['addonAA']['apiConfiguration']['item']['save_activity_logs']['label']                  = 'Activity log';
$_LANG['addonAA']['apiConfiguration']['item']['save_activity_logs']['description']            = 'Tick this field to save logs';
//
$_LANG['addonAA']['apiConfiguration']['item']['tech_firstname']['label']                               = 'Firstname';
$_LANG['addonAA']['apiConfiguration']['item']['use_admin_contact']['label']                            = 'Use Administrative Contact Details';
$_LANG['addonAA']['apiConfiguration']['item']['tech_lastname']['label']                                = 'Lastname';
$_LANG['addonAA']['apiConfiguration']['item']['tech_organization']['label']                            = 'Organization Name';
$_LANG['addonAA']['apiConfiguration']['item']['tech_title']['label']                                   = 'Job Title';
$_LANG['addonAA']['apiConfiguration']['item']['tech_addressline1']['label']                            = 'Address';
$_LANG['addonAA']['apiConfiguration']['item']['tech_phone']['label']                                   = 'Phone Number';
$_LANG['addonAA']['apiConfiguration']['item']['tech_email']['label']                                   = 'Email Address';
$_LANG['addonAA']['apiConfiguration']['item']['tech_city']['label']                                    = 'City';
$_LANG['addonAA']['apiConfiguration']['item']['tech_country']['label']                                 = 'Country';
$_LANG['addonAA']['apiConfiguration']['item']['tech_fax']['label']                                     = 'Fax Number';
$_LANG['addonAA']['apiConfiguration']['item']['tech_postalcode']['label']                              = 'Zip Code';
$_LANG['addonAA']['apiConfiguration']['item']['tech_region']['label']                                  = 'State/Region';

$_LANG['addonAA']['apiConfiguration']['item']['testConnection']['content'] = 'Test Connection';
$_LANG['addonAA']['apiConfiguration']['item']['saveItem']['label']         = 'Save';
$_LANG['addonAA']['pagesLabels']['label']['productsConfiguration']         = 'Products Configuration';
$_LANG['addonAA']['pagesLabels']['label']['productsCreator']               = 'Products Creator';
$_LANG['addonAA']['pagesLabels']['apiConfiguration']['saveItem']           = 'Save';

$_LANG['addonAA']['apiConfiguration']['messages']['api_connection_success'] = 'Connection established.';


$_LANG['addonAA']['productsConfiguration']['sslCenterProduct']    = 'SSLCenter Product:';
$_LANG['addonAA']['productsConfiguration']['productName']         = 'Product Name:';
$_LANG['addonAA']['productsConfiguration']['configurableOptions'] = 'Configurable Options:';
$_LANG['addonAA']['productsConfiguration']['createConfOptions']   = 'Generate';
$_LANG['addonAA']['productsConfiguration']['editPrices']          = 'Edit Prices';
$_LANG['addonAA']['productsConfiguration']['autoSetup']           = 'Auto Setup:';
$_LANG['addonAA']['productsConfiguration']['autoSetupOrder']      = 'Automatically setup the product as soon as an order is placed';
$_LANG['addonAA']['productsConfiguration']['autoSetupPayment']    = 'Automatically setup the product as soon as the first payment is received';
$_LANG['addonAA']['productsConfiguration']['autoSetupOn']         = 'Automatically setup the product when you manually accept a pending order';
$_LANG['addonAA']['productsConfiguration']['autoSetupOff']        = 'Do not automatically setup this product';
$_LANG['addonAA']['productsConfiguration']['months']              = 'Max Months:';
$_LANG['addonAA']['productsConfiguration']['enableSans']          = 'Enable SANs:';
$_LANG['addonAA']['productsConfiguration']['includedSans']        = 'Included SANs:';
$_LANG['addonAA']['productsConfiguration']['status']              = 'Status:';

$_LANG['addonAA']['productsConfiguration']['statusEnable']  = 'Enable';
$_LANG['addonAA']['productsConfiguration']['statusDisable'] = 'Disable';


$_LANG['addonAA']['productsConfiguration']['paymentType']          = 'Payment Type:';
$_LANG['addonAA']['productsConfiguration']['priceAutoDownlaod']    = 'Price Auto Download:';
$_LANG['addonAA']['productsConfiguration']['commission']           = 'Commission[%]:';
$_LANG['addonAA']['productsConfiguration']['paymentTypeFree']      = 'Free';
$_LANG['addonAA']['productsConfiguration']['paymentTypeRecurring'] = 'Recurring';
$_LANG['addonAA']['productsConfiguration']['paymentTypeOneTime']   = 'One Time';

$_LANG['addonAA']['productsConfiguration']['pricing']             = 'Pricing:';
$_LANG['addonAA']['productsConfiguration']['pricingMonthly']      = 'One Time/Monthly';
$_LANG['addonAA']['productsConfiguration']['pricingQuarterly']    = 'Quarterly';
$_LANG['addonAA']['productsConfiguration']['pricingSemiAnnually'] = 'Semi-Annually';
$_LANG['addonAA']['productsConfiguration']['pricingAnnually']     = 'Annually';
$_LANG['addonAA']['productsConfiguration']['pricingBiennially']   = 'Biennially';
$_LANG['addonAA']['productsConfiguration']['pricingTriennially']  = 'Triennially';

$_LANG['addonAA']['productsConfiguration']['pricingSetupFee']        = 'Setup Fee';
$_LANG['addonAA']['productsConfiguration']['pricingPrice']           = 'Price';
$_LANG['addonAA']['productsConfiguration']['pricingCommissionPrice'] = 'Price With Commission';
$_LANG['addonAA']['productsConfiguration']['pricingEnable']          = 'Enable';

$_LANG['addonAA']['productsConfiguration']['save']         = 'Save';
$_LANG['addonAA']['productsConfiguration']['messages'][''] = '';


$_LANG['addonAA']['productsCreator']['singleProductCreator'] = 'Single Product Creator';
$_LANG['addonAA']['productsCreator']['sslCenterProduct']     = 'SSLCenter Product:';
$_LANG['addonAA']['productsCreator']['productName']          = 'Product Name:';
$_LANG['addonAA']['productsCreator']['productGroup']         = 'Product Group:';
$_LANG['addonAA']['productsCreator']['autoSetup']            = 'Auto Setup:';
$_LANG['addonAA']['productsCreator']['autoSetupOrder']       = 'Automatically setup the product as soon as an order is placed';
$_LANG['addonAA']['productsCreator']['autoSetupPayment']     = 'Automatically setup the product as soon as the first payment is received';
$_LANG['addonAA']['productsCreator']['autoSetupOn']          = 'Automatically setup the product when you manually accept a pending order';
$_LANG['addonAA']['productsCreator']['autoSetupOff']         = 'Do not automatically setup this product';
$_LANG['addonAA']['productsCreator']['months']               = ' Months:';



$_LANG['addonAA']['productsCreator']['enableSans']   = 'Enable SANs:';
$_LANG['addonAA']['productsCreator']['includedSans'] = 'Included SANs:';

$_LANG['addonAA']['productsCreator']['pricing']             = 'Pricing:';
$_LANG['addonAA']['productsCreator']['pricingMonthly']      = 'One Time/Monthly';
$_LANG['addonAA']['productsCreator']['pricingQuarterly']    = 'Quarterly';
$_LANG['addonAA']['productsCreator']['pricingSemiAnnually'] = 'Semi-Annually';
$_LANG['addonAA']['productsCreator']['pricingAnnually']     = 'Annually';
$_LANG['addonAA']['productsCreator']['pricingBiennially']   = 'Biennially';
$_LANG['addonAA']['productsCreator']['pricingTriennially']  = 'Triennially';

$_LANG['addonAA']['productsCreator']['pricingSetupFee'] = 'Setup Fee';
$_LANG['addonAA']['productsCreator']['pricingPrice']    = 'Price';
$_LANG['addonAA']['productsCreator']['pricingEnable']   = 'Enable';
$_LANG['addonAA']['productsCreator']['saveSingle']      = 'Create Single Product';

$_LANG['addonAA']['productsCreator']['multipleProductCreator'] = 'Multiple Product Creator';
$_LANG['addonAA']['productsCreator']['saveMultiple']           = 'Create Multiple Products';

$_LANG['addonAA']['productsCreator']['messages']['mass_product_created']    = 'Products has been added as hidden, go to `Products Configuration` to unhide it, before that verify product configuration and set prices.';
$_LANG['addonAA']['productsCreator']['messages']['single_product_created']  = 'Product has been added as hidden, go to `Products Configuration` to unhide it, before that verify product configuration.';
$_LANG['addonAA']['productsCreator']['messages']['no_product_group_found']  = 'No product group found.';
$_LANG['addonAA']['productsCreator']['messages']['api_product_not_chosen']  = 'SSLCenter product not chosen.';
$_LANG['addonAA']['productsCreator']['messages']['api_configuration_empty'] = 'API configuration are empty';

$_LANG['addonAA']['productsConfiguration']['messages']['product_saved']          = 'Product saved.';
$_LANG['addonAA']['productsConfiguration']['messages']['configurable_generated'] = 'Configurable options for product was successfully generated.';

$_LANG['addonAA']['productsConfiguration']['messages']['api_configuration_empty'] = 'API configuration are empty';

$_LANG['addonAA']['pagesLabels']['label']['importSSLOrder']                      = 'Import SSL Order';
$_LANG['addonAA']['importSSLOrder']['header']                                    = 'Import SSL Order';
$_LANG['addonAA']['importSSLOrder']['order_id']['label']                         = 'API Order ID';
$_LANG['addonAA']['importSSLOrder']['client_id']['label']                        = 'Client';
$_LANG['addonAA']['importSSLOrder']['importSSL']['content']                      = 'Import';
$_LANG['addonAA']['importSSLOrder']['messages']['import_success']                = 'SSL order has been imported successfully.';
$_LANG['addonAA']['importSSLOrder']['messages']['order_id_not_provided']         = 'API order ID has been not provided.';
$_LANG['addonAA']['importSSLOrder']['messages']['client_id_not_provided']        = 'Client ID has been not provided.';
$_LANG['addonAA']['importSSLOrder']['messages']['ssl_order_already_exist']       = 'SLL order with provided ID already exist in system.';
$_LANG['addonAA']['importSSLOrder']['messages']['ssl_order_product_not_exist']   = 'Product for provided SSL order ID not exist in system.';
$_LANG['addonAA']['importSSLOrder']['messages']['order_create_error']            = 'Unable to create order';
$_LANG['addonAA']['importSSLOrder']['messages']['no_payment_gateway_error']      = 'No payment gateway has been configured.';
$_LANG['addonAA']['importSSLOrder']['messages']['order_cancelled_import_unable'] = 'Unable to import cancelled SSL order.';

$_LANG['addonAA']['userCommissions']['integrationCode']['header']         = 'Integration Code';
$_LANG['addonAA']['userCommissions']['pleaseNote']                        = 'Please Note';
$_LANG['addonAA']['userCommissions']['info']                              = 'In order to diplay product prices with additional commission in the Client Area: ';
$_LANG['addonAA']['userCommissions']['info1']                             = '1. Open the file';
$_LANG['addonAA']['userCommissions']['info2']                             = '2. Add this code on the top of file';
$_LANG['addonAA']['userCommissions']['info3']                             = '3. Open the file';
$_LANG['addonAA']['userCommissions']['info4']                             = '4. Add this code on the top of file';
$_LANG['addonAA']['pagesLabels']['label']['userCommissions']              = 'Commission Rules';
$_LANG['addonAA']['userCommissions']['title']                             = 'Commission Rules';
$_LANG['addonAA']['userCommissions']['addNewCommissionRule']              = 'Add New Rule';
$_LANG['addonAA']['userCommissions']['editItem']                          = 'Edit';
$_LANG['addonAA']['userCommissions']['deleteItem']                        = 'Remove';
$_LANG['addonAA']['userCommissions']['messages']['addSuccess']            = 'Commission rule added successfully.';
$_LANG['addonAA']['userCommissions']['messages']['removeSuccess']         = 'Commission rule removed successfully.';
$_LANG['addonAA']['userCommissions']['messages']['updateSuccess']         = 'Commission rule updated successfully.';
$_LANG['addonAA']['userCommissions']['messages']['clientIDNotProvided']   = 'Client ID has been not provided.';
$_LANG['addonAA']['userCommissions']['messages']['ruleIDNotProvided']     = 'Rule ID has been not provided.';
$_LANG['addonAA']['userCommissions']['messages']['productIDNotProvided']  = 'Product ID has been not provided.';
$_LANG['addonAA']['userCommissions']['messages']['commissionNotProvided'] = 'Commission has been not provided.';

$_LANG['addonAA']['userCommissions']['table']['client']                       = 'Client';
$_LANG['addonAA']['userCommissions']['table']['product']                      = 'Product';
$_LANG['addonAA']['userCommissions']['table']['commission']                   = 'Commission[%]';
$_LANG['addonAA']['userCommissions']['table']['monthly/onetime']              = 'Monthly/One Time';
$_LANG['addonAA']['userCommissions']['table']['quarterly']                    = 'Quarterly';
$_LANG['addonAA']['userCommissions']['table']['semiannually']                 = 'Semiannually';
$_LANG['addonAA']['userCommissions']['table']['annually']                     = 'Annually';
$_LANG['addonAA']['userCommissions']['table']['biennially']                   = 'Biennially';
$_LANG['addonAA']['userCommissions']['table']['triennially']                  = 'Triennially';
$_LANG['addonAA']['userCommissions']['table']['actions']                      = 'Actions';
$_LANG['addonAA']['userCommissions']['modal']['pleaseSelecetOnePlaceholder']  = 'Please select one...';
$_LANG['addonAA']['userCommissions']['modal']['selectClientFirstPlaceholder'] = 'Please select a client first...';
$_LANG['addonAA']['userCommissions']['modal']['pleaseSelectProductFirst']     = 'Please select a product first...';
$_LANG['addonAA']['userCommissions']['modal']['noDataAvailable']              = 'No data available.';
$_LANG['addonAA']['userCommissions']['modal']['noClientAvailable']            = 'No client available.';
$_LANG['addonAA']['userCommissions']['modal']['noProductAvailable']           = 'No product available.';
$_LANG['addonAA']['userCommissions']['table']['basePrice']                    = 'Base Price: ';
$_LANG['addonAA']['userCommissions']['table']['priceWithCommission']          = 'With Comm.: ';

$_LANG['addonAA']['userCommissions']['modal']['addCommissionRule']          = 'Add New Commission Rule';
$_LANG['addonAA']['userCommissions']['modal']['client']                     = 'Client';
$_LANG['addonAA']['userCommissions']['modal']['product']                    = 'Product';
$_LANG['addonAA']['userCommissions']['modal']['commission']                 = 'Commission[%]';
$_LANG['addonAA']['userCommissions']['modal']['add']                        = 'Add';
$_LANG['addonAA']['userCommissions']['modal']['edit']                       = 'Save Changes';
$_LANG['addonAA']['userCommissions']['modal']['close']                      = 'Close';
$_LANG['addonAA']['userCommissions']['modal']['productPrice']               = 'Product Price';
$_LANG['addonAA']['userCommissions']['modal']['productPriceWithCommission'] = 'Product Price With Commission';
$_LANG['addonAA']['userCommissions']['modal']['monthly/onetime']            = 'Monthly/One Time';
$_LANG['addonAA']['userCommissions']['modal']['quarterly']                  = 'Quarterly';
$_LANG['addonAA']['userCommissions']['modal']['semiannually']               = 'Semiannually';
$_LANG['addonAA']['userCommissions']['modal']['annually']                   = 'Annually';
$_LANG['addonAA']['userCommissions']['modal']['biennially']                 = 'Biennially';
$_LANG['addonAA']['userCommissions']['modal']['triennially']                = 'Triennially';
$_LANG['addonAA']['userCommissions']['modal']['removeRule']                 = 'Remove Commission Rule';
$_LANG['addonAA']['userCommissions']['modal']['remove']                     = 'Remove';
$_LANG['addonAA']['userCommissions']['modal']['removeRuleInfo']             = 'You are about to remove commission rule, this procedure is irreversible.';


$_LANG['anErrorOccurred'] = 'An error occurred';

$_LANG['pagesLabels']['label']['orders']       = 'Import SSL Order';
$_LANG['addonCA']['sslSummary']['title']       = 'SSL Orders Summary';
$_LANG['addonCA']['sslSummary']['total']       = 'Total Orders';
$_LANG['addonCA']['sslSummary']['unpaid']      = 'Unpaid Orders';
$_LANG['addonCA']['sslSummary']['processing']  = 'Processing';
$_LANG['addonCA']['sslSummary']['expiresSoon'] = 'Expires Soon';

$_LANG['addonCA']['sslSummaryOrdersPage']['pageTitle']['total']        = 'Total Orders';
$_LANG['addonCA']['sslSummaryOrdersPage']['pageTitle']['unpaid']       = 'Unpaid Orders';
$_LANG['addonCA']['sslSummaryOrdersPage']['pageTitle']['processing']   = 'Processing Orders';
$_LANG['addonCA']['sslSummaryOrdersPage']['pageTitle']['expires_soon'] = 'Expires Soon Orders';
$_LANG['addonCA']['sslSummaryOrdersPage']['Product/Service']           = 'Product/Service';
$_LANG['addonCA']['sslSummaryOrdersPage']['Pricing']                   = 'Pricing';
$_LANG['addonCA']['sslSummaryOrdersPage']['Next Due Date']             = 'Next Due Date';
$_LANG['addonCA']['sslSummaryOrdersPage']['Status']                    = 'Status';

$_LANG['invalidEmailAddress']           = 'Email Address is incorrect';
$_LANG['csrCodeGeneraterdSuccessfully'] = 'CSR code has been generated successfully';
$_LANG['invalidCountryCode']            = 'Country code is incorrect';
$_LANG['csrCodeGeneraterFailed']        = 'Generate CSR code has been failed';
