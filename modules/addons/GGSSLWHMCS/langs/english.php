<?php

$_LANG['token']        = ', Error Token:';
$_LANG['generalError'] = 'Something has gone wrong. Check logs and contact admin';

//ggssl configuration
$_LANG['addonAA']['pagesLabels']['label']['apiConfiguration']                                          = 'Configuration';
$_LANG['addonAA']['apiConfiguration']['crons']['header']                                 = 'Crons';
//synchronization cron
$_LANG['addonAA']['apiConfiguration']['cronSynchronization']['pleaseNote']                             = 'Please Note:';
$_LANG['addonAA']['apiConfiguration']['cronSynchronization']['info']                                   = 'In order to enable automatic synchronization, please set up a following cron command line (every hour suggested):';
$_LANG['addonAA']['apiConfiguration']['cronSynchronization']['commandLine']['cronFrequency']           = '0 */1 * * *';
//summary order cron
$_LANG['addonAA']['apiConfiguration']['cronSSLSummaryStats']['pleaseNote']                             = 'Please Note:';
$_LANG['addonAA']['apiConfiguration']['cronSSLSummaryStats']['info']                                   = 'In order to enable load current SSL orders status, please set up a following cron command line (every 4 hours suggested):';
$_LANG['addonAA']['apiConfiguration']['cronSSLSummaryStats']['commandLine']['cronFrequency']           = '1 */4 * * *';
//customers notification and creating renewals
$_LANG['addonAA']['apiConfiguration']['cronRenewal']['pleaseNote']                             = 'Please Note:';
$_LANG['addonAA']['apiConfiguration']['cronRenewal']['info']                                   = 'In order to send customers notifications of expiring services and create renewal invoices for services that expire within the selected number of days, set the following command line cron (once a day suggested):';
$_LANG['addonAA']['apiConfiguration']['cronRenewal']['commandLine']['cronFrequency']           = '0 0 * * *';
//customers send certificate
$_LANG['addonAA']['apiConfiguration']['cronSendCertificate']['pleaseNote']                             = 'Please Note:';
$_LANG['addonAA']['apiConfiguration']['cronSendCertificate']['info']                                   = 'In order to send a certificate to the client when the SSL order changes to active status, set the following command line cron (every 3 hours suggested):';
$_LANG['addonAA']['apiConfiguration']['cronSendCertificate']['commandLine']['cronFrequency']           = '0 3 * * *';
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
$_LANG['addonAA']['apiConfiguration']['item']['summary_expires_soon_days']['label']                    = 'Expires Soon';
$_LANG['addonAA']['apiConfiguration']['item']['summary_expires_soon_days']['description']              = 'Count SSL order for statistics, if there are fewer or equal days to expire than the selected ones.';
$_LANG['addonAA']['apiConfiguration']['item']['send_certificate_template']['label']                    = 'Send Certificate Email Template';
$_LANG['addonAA']['apiConfiguration']['item']['send_certificate_template']['description']              = 'To send an SSL certificate through the chosen template, edit it and place the {$ssl_certyficate} field in it.';
//
$_LANG['addonAA']['apiConfiguration']['item']['renewal_settings_legend']['label']                      = 'Renewal Settings';
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


$_LANG['addonAA']['productsConfiguration']['goGetSSLProduct']     = 'GoGetSSL Product:';
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

$_LANG['addonAA']['productsConfiguration']['pricingSetupFee'] = 'Setup Fee';
$_LANG['addonAA']['productsConfiguration']['pricingPrice']    = 'Price';
$_LANG['addonAA']['productsConfiguration']['pricingEnable']   = 'Enable';

$_LANG['addonAA']['productsConfiguration']['save']         = 'Save';
$_LANG['addonAA']['productsConfiguration']['messages'][''] = '';


$_LANG['addonAA']['productsCreator']['singleProductCreator'] = 'Single Product Creator';
$_LANG['addonAA']['productsCreator']['goGetSSLProduct']      = 'GoGetSSL Product:';
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
$_LANG['addonAA']['productsCreator']['messages']['api_product_not_chosen']  = 'GoGetSSL product not chosen.';
$_LANG['addonAA']['productsCreator']['messages']['api_configuration_empty'] = 'API configuration are empty';

$_LANG['addonAA']['productsConfiguration']['messages']['product_saved']          = 'Product saved.';
$_LANG['addonAA']['productsConfiguration']['messages']['configurable_generated'] = 'Configurable options for product was successfully generated.';

$_LANG['addonAA']['productsConfiguration']['messages']['api_configuration_empty'] = 'API configuration are empty';

$_LANG['addonAA']['pagesLabels']['label']['importSSLOrder']                      = 'Import SSL Order';
$_LANG['addonAA']['importSSLOrder']['header']                                    = 'Import SSL Order';
$_LANG['addonAA']['importSSLOrder']['order_id']['label']                         = 'API Order ID';
$_LANG['addonAA']['importSSLOrder']['client_id']['label']                        = 'Client';
$_LANG['addonAA']['importSSLOrder']['importSSL']['content']                      = 'Import';
$_LANG['addonAA']['importSSLOrder']['messages']['import_success']                = 'SSL order has been imported succesfully.';
$_LANG['addonAA']['importSSLOrder']['messages']['order_id_not_provided']         = 'API order ID has been not provided.';
$_LANG['addonAA']['importSSLOrder']['messages']['client_id_not_provided']        = 'Client ID has been not provided.';
$_LANG['addonAA']['importSSLOrder']['messages']['ssl_order_already_exist']       = 'SLL order with provided ID already exist in system.';
$_LANG['addonAA']['importSSLOrder']['messages']['ssl_order_product_not_exist']   = 'Product for provided SSL order ID not exist in system.';
$_LANG['addonAA']['importSSLOrder']['messages']['order_create_error']            = 'Unable to create order';
$_LANG['addonAA']['importSSLOrder']['messages']['order_cancelled_import_unable'] = 'Unable to import cancelled SSL order.';


$_LANG['anErrorOccurred'] = 'An error occurred';

$_LANG['addonCA']['sslSummary']['title'] = 'SSL Orders Summary';
$_LANG['addonCA']['sslSummary']['total'] = 'Total Orders';
$_LANG['addonCA']['sslSummary']['unpaid'] = 'Unpaid Orders';
$_LANG['addonCA']['sslSummary']['processing'] = 'Processing';
$_LANG['addonCA']['sslSummary']['expiresSoon'] = 'Expires Soon';

$_LANG['sslSummarySidebarTitle'] = 'SSL Orders Summary';
$_LANG['sslSummarySidebarTotal'] = 'Total Orders';
$_LANG['sslSummarySidebarUnpaid'] = 'Unpaid Orders';
$_LANG['sslSummarySidebarProcessing'] = 'Processing';
$_LANG['sslSummarySidebarExpiresSoon'] = 'Expires Soon';

$_LANG['invalidEmailAddress']           = 'Email Address is incorrect';
$_LANG['csrCodeGeneraterdSuccessfully'] = 'CSR code has been generated successfully';
$_LANG['invalidCountryCode']            = 'Country code is incorrect';
$_LANG['csrCodeGeneraterFailed']        = 'Generate CSR code has been failed';
