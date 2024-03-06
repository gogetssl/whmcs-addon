<?php

$_LANG['token']        = ', Fejl-token:';
$_LANG['generalError'] = 'Noget gik galt. Gennemse logfilerne og kontakt administratoren';

//SSLCENTER configuration
$_LANG['addonAA']['pagesLabels']['label']['apiConfiguration']                                          = 'Konfiguration';
$_LANG['addonAA']['apiConfiguration']['crons']['header']                                               = 'Crons';
//synchronization cron
$_LANG['addonAA']['apiConfiguration']['DailyCron']['pleaseNote']                             = 'Bemærk:';
$_LANG['addonAA']['apiConfiguration']['DailyCron']['info']                                   = 'For at kunne aktivere automatisk daglig synkronisering skal du opsætte følgende cron job:';
$_LANG['addonAA']['apiConfiguration']['DailyCron']['commandLine']['cronFrequency']           = '0 0 * * *';
//processing cron
$_LANG['addonAA']['apiConfiguration']['cronProcessing']['pleaseNote']                             = 'Bemærk:';
$_LANG['addonAA']['apiConfiguration']['cronProcessing']['info']                                   = 'For at kunne aktivere automatisk synkronisering af behandling af ordrer hvert 5. minut skal du opsætte følgende cron job:';
$_LANG['addonAA']['apiConfiguration']['cronProcessing']['commandLine']['cronFrequency']           = '*/5 * * * *';

//synchronization cron
$_LANG['addonAA']['apiConfiguration']['cronSynchronization']['pleaseNote']                             = 'Bemærk:';
$_LANG['addonAA']['apiConfiguration']['cronSynchronization']['info']                                   = 'For at kunne aktivere automatisk synkronisering skal du opsætte følgende cron job (hver time anbefalet):';
$_LANG['addonAA']['apiConfiguration']['cronSynchronization']['commandLine']['cronFrequency']           = '0 */1 * * *';
//summary order cron
$_LANG['addonAA']['apiConfiguration']['cronSSLSummaryStats']['pleaseNote']                             = 'Bemærk:';
$_LANG['addonAA']['apiConfiguration']['cronSSLSummaryStats']['info']                                   = 'For at kunne aktivere indlæsning af nuværende SSL ordrers status skal du opsætte følgende cron job (hver 4. time anbefalet):';
$_LANG['addonAA']['apiConfiguration']['cronSSLSummaryStats']['commandLine']['cronFrequency']           = '1 */4 * * *';
//customers notification and creating renewals
$_LANG['addonAA']['apiConfiguration']['cronRenewal']['pleaseNote']                                     = 'Bemærk:';
$_LANG['addonAA']['apiConfiguration']['cronRenewal']['info']                                           = 'For at kunne sende en notifikation til kunden om services, der udløber, og oprette en faktura til fornyelse for services, der udløber inden det valgte antal dage, skal du opsætte følgende cron job (én gang dagligt anbefalet):';
$_LANG['addonAA']['apiConfiguration']['cronRenewal']['commandLine']['cronFrequency']                   = '0 0 * * *';
//customers send certificate
$_LANG['addonAA']['apiConfiguration']['cronSendCertificate']['pleaseNote']                             = 'Bemærk:';
$_LANG['addonAA']['apiConfiguration']['cronSendCertificate']['info']                                   = 'For at kunne sende et certifikat til kunden, når ordren for SSL ændres til aktiv status skal du opsætte følgende cron job (hver 3. time anbefalet):';
$_LANG['addonAA']['apiConfiguration']['cronSendCertificate']['commandLine']['cronFrequency']           = '0 */3 * * *';
//customers send certificate
$_LANG['addonAA']['apiConfiguration']['cronPriceUpdater']['pleaseNote']                             = 'Bemærk:';
$_LANG['addonAA']['apiConfiguration']['cronPriceUpdater']['info']                                   = 'For at kunne synkronisere produktpriserne i WHMCS med produktpriserne fra API\'en skal du opsætte følgende cron job (hver 3. dag anbefalet):';
$_LANG['addonAA']['apiConfiguration']['cronPriceUpdater']['commandLine']['cronFrequency']           = '0 0 */3 * *';
//customers send certificate
$_LANG['addonAA']['apiConfiguration']['cronCertificateDetailsUpdater']['pleaseNote']                             = 'Bemærk:';
$_LANG['addonAA']['apiConfiguration']['cronCertificateDetailsUpdater']['info']                                   = 'For at kunne synkronisere certifikatdetaljer i WHMCS med certifikatdetaljer fra API\'en skal du opsætte følgende cron job (én gang dagligt anbefalet):';
$_LANG['addonAA']['apiConfiguration']['cronCertificateDetailsUpdater']['commandLine']['cronFrequency']           = '0 0 * * *';
//
$_LANG['addonAA']['apiConfiguration']['item']['price_rate']['label']                                   = 'Rate for currency from SSLCENTER';
$_LANG['addonAA']['apiConfiguration']['item']['rate']['label']                                         = 'Rate';
$_LANG['addonAA']['apiConfiguration']['item']['header']                                                = 'API Konfiguration';
$_LANG['addonAA']['apiConfiguration']['item']['api_login']['label']                                    = 'Login';
$_LANG['addonAA']['apiConfiguration']['item']['api_password']['label']                                 = 'Adgangskode';
$_LANG['addonAA']['apiConfiguration']['item']['tech_legend']['label']                                  = 'Teknisk Kontakt';
$_LANG['addonAA']['apiConfiguration']['item']['csr_generator_legend']['label']                         = 'CSR Generator';
$_LANG['addonAA']['apiConfiguration']['item']['display_csr_generator']['label']                        = 'Tillad brugen af CSR Generator';
$_LANG['addonAA']['apiConfiguration']['item']['profile_data_csr']['label']                             = 'Use Profile Data for CSR';
$_LANG['addonAA']['apiConfiguration']['item']['auto_install_cpanel']['']['autoInstallCpanel']          = 'Tick this box if you want to use automatic certificate installation in cPanel';
$_LANG['addonAA']['apiConfiguration']['item']['default_csr_generator_country']['description']          = 'Standardvalget';
$_LANG['addonAA']['apiConfiguration']['item']['display_ca_summary']['label']                           = 'Vis Ordreoversigt';
$_LANG['addonAA']['apiConfiguration']['item']['client_area_summary_orders']['label']                   = 'Klientområde Ordreoversigt';
$_LANG['addonAA']['apiConfiguration']['item']['validation_settings']['label']                          = 'Valideringsindstillinger';
$_LANG['addonAA']['apiConfiguration']['item']['disable_email_validation']['label']                     = 'Deaktiver validering via e-mail';
$_LANG['addonAA']['apiConfiguration']['item']['email_whois']['label']                                  = 'Email WHOIS';
$_LANG['addonAA']['apiConfiguration']['item']['summary_expires_soon_days']['label']                    = 'Udløber snart';
$_LANG['addonAA']['apiConfiguration']['item']['summary_expires_soon_days']['description']              = 'Medregn SSL-ordre til statistik, hvis der er færre eller lige dage, der skal udløbe end de valgte';
$_LANG['addonAA']['apiConfiguration']['item']['send_certificate_template']['label']                    = 'Send Certifikat E-mailskabelon';
$_LANG['addonAA']['apiConfiguration']['item']['send_certificate_template']['description']              = 'For at sende et SSL-certifikat igennem den valgte skabelon skal du ændre skabelonen og placere {$ssl_certyficate} i skabelonen.';
//
$_LANG['addonAA']['apiConfiguration']['item']['data_migration_legend']['label']                        = 'Migrering af Data og Konfiguration';
$_LANG['addonAA']['apiConfiguration']['item']['data_migration']['content']                             = 'Migrer';
$_LANG['addonAA']['apiConfiguration']['modal']['import']                                               = 'Migrer';
$_LANG['addonAA']['apiConfiguration']['modal']['close']                                                = 'Luk';
$_LANG['addonAA']['apiConfiguration']['modal']['migrationData']                                        = 'Importer Data og Konfiguration';
$_LANG['addonAA']['apiConfiguration']['migrationOldModuleDataExixts']                                  = 'Der er produkter eller services tilknyttet GGSL WHMCS modulet:';
$_LANG['addonAA']['apiConfiguration']['migrationProductIDs']                                           = 'Produkt-ID\'er: ';
$_LANG['addonAA']['apiConfiguration']['migrationServiceIDs']                                           = 'Service-ID\'er: ';
$_LANG['addonAA']['apiConfiguration']['migrationPerformMigration']                                     = 'Udfør migrering af data for at tilknytte konfiguration og data med SSLCenter WHMCS modulet.';
$_LANG['addonAA']['apiConfiguration']['modal']['dataMigrationInfo']                                    = 'Du er ved at migrere data og konfiguration fra GGSSL WHMCS modulet. Denne handling kan ikke fortrydes.';
$_LANG['addonAA']['apiConfiguration']['modal']['dataMigrationInfo2']                                   = 'Handlinger, der bliver foretaget:';
$_LANG['addonAA']['apiConfiguration']['modal']['dataMigrationInfoAction'][0]                           = 'importering af addon-konfigurationen';
$_LANG['addonAA']['apiConfiguration']['modal']['dataMigrationInfoAction'][1]                           = 'opdatering af eksisterende produkter (ændring af tilknyttet modul)';
$_LANG['addonAA']['apiConfiguration']['modal']['dataMigrationInfoAction'][2]                           = 'opdatering af eksisterende services (ændring af tilknyttet modul)';
$_LANG['addonAA']['apiConfiguration']['messages']['data_migration_success']                            = 'Importering af Data og Konfiguration er blevet importeret. Denne side genindlæser automatisk om 5 sekunder.';
//
$_LANG['addonAA']['apiConfiguration']['item']['renewal_settings_legend']['label']                      = 'Fornyelsesindstillinger';
$_LANG['addonAA']['apiConfiguration']['item']['logs_settings_legend']['label']                      = 'Logindstillinger';
$_LANG['addonAA']['apiConfiguration']['item']['auto_renew_invoice_reccuring']['label']                 = 'Gentagende Ordrer';
$_LANG['addonAA']['apiConfiguration']['item']['auto_renew_invoice_reccuring']['description']           = 'Opret automatisk faktura for fornyelse';
$_LANG['addonAA']['apiConfiguration']['item']['send_expiration_notification_reccuring']['label']       = '';
$_LANG['addonAA']['apiConfiguration']['item']['renew_invoice_days_reccuring']['description']           = 'dage før udløb';
$_LANG['addonAA']['apiConfiguration']['item']['send_expiration_notification_reccuring']['description'] = 'Send notifikation om udløb';
$_LANG['addonAA']['apiConfiguration']['item']['auto_renew_invoice_one_time']['label']                  = 'Engangsordrer';
$_LANG['addonAA']['apiConfiguration']['item']['auto_renew_invoice_one_time']['description']            = 'Opret automatisk faktura for fornyelse';
$_LANG['addonAA']['apiConfiguration']['item']['send_expiration_notification_one_time']['label']        = '';
$_LANG['addonAA']['apiConfiguration']['item']['renew_invoice_days_one_time']['description']            = 'dage før udløb';
$_LANG['addonAA']['apiConfiguration']['item']['send_expiration_notification_one_time']['description']  = 'Send notifikation om udløb';

$_LANG['addonAA']['apiConfiguration']['item']['renewal_invoice_status_unpaid']['label']                  = '';
$_LANG['addonAA']['apiConfiguration']['item']['renewal_invoice_status_unpaid']['description']            = 'Set the status of Unpaid for renewal invoices (default is payment pending)';
$_LANG['addonAA']['apiConfiguration']['item']['automatic_processing_of_renewal_orders']['label']                  = '';
$_LANG['addonAA']['apiConfiguration']['item']['automatic_processing_of_renewal_orders']['description']            = 'Automatisk behandling af fornyelsesordrer';
$_LANG['addonAA']['apiConfiguration']['item']['renew_new_order']['label']                  = '';
$_LANG['addonAA']['apiConfiguration']['item']['renew_new_order']['description']            = 'Forny ordre igennem eksisterende ordre';
$_LANG['addonAA']['apiConfiguration']['item']['visible_renew_button']['label']                  = '';
$_LANG['addonAA']['apiConfiguration']['item']['visible_renew_button']['description']            = 'Synlig "Forny"-knap i Klientområdet';
$_LANG['addonAA']['apiConfiguration']['item']['save_activity_logs']['label']                  = 'Aktivitetslog';
$_LANG['addonAA']['apiConfiguration']['item']['save_activity_logs']['description']            = 'Afkryds for at gemme logs';
//
$_LANG['addonAA']['apiConfiguration']['item']['tech_firstname']['label']                               = 'Fornavn';
$_LANG['addonAA']['apiConfiguration']['item']['use_admin_contact']['label']                            = 'Brug Kontaktoplysninger for Administrativ Kontakt';
$_LANG['addonAA']['apiConfiguration']['item']['tech_lastname']['label']                                = 'Efternavn';
$_LANG['addonAA']['apiConfiguration']['item']['tech_organization']['label']                            = 'Organisationens navn';
$_LANG['addonAA']['apiConfiguration']['item']['tech_title']['label']                                   = 'Jobtitel';
$_LANG['addonAA']['apiConfiguration']['item']['tech_addressline1']['label']                            = 'Adresse';
$_LANG['addonAA']['apiConfiguration']['item']['tech_phone']['label']                                   = 'Telefonnummer';
$_LANG['addonAA']['apiConfiguration']['item']['tech_email']['label']                                   = 'E-mailadresse';
$_LANG['addonAA']['apiConfiguration']['item']['tech_city']['label']                                    = 'By';
$_LANG['addonAA']['apiConfiguration']['item']['tech_country']['label']                                 = 'Land';
$_LANG['addonAA']['apiConfiguration']['item']['tech_fax']['label']                                     = 'Fax';
$_LANG['addonAA']['apiConfiguration']['item']['tech_postalcode']['label']                              = 'Postnummer';
$_LANG['addonAA']['apiConfiguration']['item']['tech_region']['label']                                  = 'Stat/Region';

$_LANG['addonAA']['apiConfiguration']['item']['testConnection']['content'] = 'Test Forbindelse';
$_LANG['addonAA']['apiConfiguration']['item']['saveItem']['label']         = 'Gem';
$_LANG['addonAA']['pagesLabels']['label']['productsConfiguration']         = 'Produktkonfiguration';
$_LANG['addonAA']['pagesLabels']['label']['productsCreator']               = 'Produktopretter';
$_LANG['addonAA']['pagesLabels']['apiConfiguration']['saveItem']           = 'Gem';

$_LANG['addonAA']['apiConfiguration']['messages']['api_connection_success'] = 'Forbindelse etableret.';


$_LANG['addonAA']['productsConfiguration']['sslCenterProduct']    = 'SSLCenter Produkt:';
$_LANG['addonAA']['productsConfiguration']['productName']         = 'Produktnavn:';
$_LANG['addonAA']['productsConfiguration']['configurableOptions'] = 'Konfigurerbare indstillinger SAN:';
$_LANG['addonAA']['productsConfiguration']['configurableOptionsWildcard'] = 'Konfigurerbare indstillinger Wildcard SAN:';
$_LANG['addonAA']['productsConfiguration']['createConfOptions']   = 'Generer';
$_LANG['addonAA']['productsConfiguration']['editPrices']          = 'Rediger priser';
$_LANG['addonAA']['productsConfiguration']['autoSetup']           = 'Automatisk Opsætning:';
$_LANG['addonAA']['productsConfiguration']['autoSetupOrder']      = 'Opsæt produktet automatisk så snart en ordre er placeret';
$_LANG['addonAA']['productsConfiguration']['autoSetupPayment']    = 'Opsæt produktet automatisk så snart den første betaling er modtaget';
$_LANG['addonAA']['productsConfiguration']['autoSetupOn']         = 'Opsæt produktet automatisk, når du manuelt accepterer en afventende ordre';
$_LANG['addonAA']['productsConfiguration']['autoSetupOff']        = 'Opsæt ikke dette produkt automatisk';
$_LANG['addonAA']['productsConfiguration']['months']              = 'Maks. antal måneder:';
$_LANG['addonAA']['productsConfiguration']['enableSans']          = 'Aktiver SANs:';
$_LANG['addonAA']['productsConfiguration']['enableSansWildcard']  = 'Aktiver Wildcard SANs:';
$_LANG['addonAA']['productsConfiguration']['includedSans']        = 'Inkluderede SANs:';
$_LANG['addonAA']['productsConfiguration']['includedSansWildcard']= 'Included Wildcard SANs:';
$_LANG['addonAA']['productsConfiguration']['status']              = 'Status:';

$_LANG['addonAA']['productsConfiguration']['statusEnable']  = 'Aktiver';
$_LANG['addonAA']['productsConfiguration']['statusDisable'] = 'Deaktiver';


$_LANG['addonAA']['productsConfiguration']['paymentType']          = 'Betalingstype:';
$_LANG['addonAA']['productsConfiguration']['priceAutoDownlaod']    = 'Auto Download Pris:';
$_LANG['addonAA']['productsConfiguration']['commission']           = 'Komission[%]:';
$_LANG['addonAA']['productsConfiguration']['paymentTypeFree']      = 'Gratis';
$_LANG['addonAA']['productsConfiguration']['paymentTypeRecurring'] = 'Gentagende';
$_LANG['addonAA']['productsConfiguration']['paymentTypeOneTime']   = 'Én gang';

$_LANG['addonAA']['productsConfiguration']['pricing']             = 'Pris:';
$_LANG['addonAA']['productsConfiguration']['pricingMonthly']      = 'Én gang/Månedlig';
$_LANG['addonAA']['productsConfiguration']['pricingQuarterly']    = 'Kvartalvis';
$_LANG['addonAA']['productsConfiguration']['pricingSemiAnnually'] = 'Halvårlig';
$_LANG['addonAA']['productsConfiguration']['pricingAnnually']     = 'Årlig';
$_LANG['addonAA']['productsConfiguration']['pricingBiennially']   = 'Hvert andet år';
$_LANG['addonAA']['productsConfiguration']['pricingTriennially']  = 'Hvert tredje år';

$_LANG['addonAA']['productsConfiguration']['pricingSetupFee']        = 'Opsætningsgebyr';
$_LANG['addonAA']['productsConfiguration']['pricingPrice']           = 'Pris';
$_LANG['addonAA']['productsConfiguration']['pricingCommissionPrice'] = 'Pris med Kommission';
$_LANG['addonAA']['productsConfiguration']['pricingEnable']          = 'Aktiver';

$_LANG['addonAA']['productsConfiguration']['save']         = 'Gem';
$_LANG['addonAA']['productsConfiguration']['messages'][''] = '';


$_LANG['addonAA']['productsCreator']['singleProductCreator'] = 'Opret Enkelt Produkt';
$_LANG['addonAA']['productsCreator']['sslCenterProduct']     = 'SSLCenter Produkt:';
$_LANG['addonAA']['productsCreator']['productName']          = 'Produktnavn:';
$_LANG['addonAA']['productsCreator']['productGroup']         = 'Produktgruppe:';
$_LANG['addonAA']['productsCreator']['autoSetup']            = 'Automatisk Opsætning:';
$_LANG['addonAA']['productsCreator']['autoSetupOrder']       = 'Opsæt produktet automatisk så snart en ordre er placeret';
$_LANG['addonAA']['productsCreator']['autoSetupPayment']     = 'Opsæt produktet automatisk så snart den første betaling er modtaget';
$_LANG['addonAA']['productsCreator']['autoSetupOn']          = 'Opsæt produktet automatisk, når du manuelt accepterer en afventende ordre';
$_LANG['addonAA']['productsCreator']['autoSetupOff']         = 'Opsæt ikke dette produkt automatisk';
$_LANG['addonAA']['productsCreator']['months']               = ' Måneder:';



$_LANG['addonAA']['productsCreator']['enableSans']   = 'Aktiver SANs:';
$_LANG['addonAA']['productsCreator']['includedSans'] = 'Inkluderede SANs:';

$_LANG['addonAA']['productsCreator']['pricing']             = 'Pris:';
$_LANG['addonAA']['productsCreator']['pricingMonthly']      = 'Én gang/Månedlig';
$_LANG['addonAA']['productsCreator']['pricingQuarterly']    = 'Kvartalvis';
$_LANG['addonAA']['productsCreator']['pricingSemiAnnually'] = 'Halvårlig';
$_LANG['addonAA']['productsCreator']['pricingAnnually']     = 'årlig';
$_LANG['addonAA']['productsCreator']['pricingBiennially']   = 'Hvert andet år';
$_LANG['addonAA']['productsCreator']['pricingTriennially']  = 'Hvert tredje år';

$_LANG['addonAA']['productsCreator']['pricingSetupFee'] = 'Opsætningsgebyr';
$_LANG['addonAA']['productsCreator']['pricingPrice']    = 'Pris';
$_LANG['addonAA']['productsCreator']['pricingEnable']   = 'Aktiver';
$_LANG['addonAA']['productsCreator']['saveSingle']      = 'Opret Enkelt Produkt';

$_LANG['addonAA']['productsCreator']['multipleProductCreator'] = 'Opret Flere Produkter';
$_LANG['addonAA']['productsCreator']['saveMultiple']           = 'Opret Flere Produkter';

$_LANG['addonAA']['productsCreator']['messages']['mass_product_created']    = 'Produkterne er blevet oprettet som skjult. Gå til `Produktkonfiguration` for at gøre gøre det synligt. Gennemgå produktkonfigurationen og priserne for produktet først.';
$_LANG['addonAA']['productsCreator']['messages']['single_product_created']  = 'Produktet er blevet oprettet som skjult. Gå til `Produktkonfiguration` for at gøre det synligt. Gennemgå produktkonfigurationen først.';
$_LANG['addonAA']['productsCreator']['messages']['no_product_group_found']  = 'Ingen produktgruppe fundet.';
$_LANG['addonAA']['productsCreator']['messages']['api_product_not_chosen']  = 'SSLCenter produkt ikke valgt.';
$_LANG['addonAA']['productsCreator']['messages']['api_configuration_empty'] = 'API-konfigurationen er tom';

$_LANG['addonAA']['productsConfiguration']['messages']['product_saved']          = 'Produkt gemt.';
$_LANG['addonAA']['productsConfiguration']['messages']['configurable_generated'] = 'Konfigurerbare indstillinger for produktet blev genereret.';

$_LANG['addonAA']['productsConfiguration']['messages']['api_configuration_empty'] = 'API-konfigurationen er tom';

$_LANG['addonAA']['pagesLabels']['label']['importSSLOrder']                      = 'Importer SSL Ordre';
$_LANG['addonAA']['importSSLOrder']['header']                                    = 'Importer SSL Ordre';
$_LANG['addonAA']['importSSLOrder']['order_id']['label']                         = 'API-Ordre ID';
$_LANG['addonAA']['importSSLOrder']['client_id']['label']                        = 'Klient';
$_LANG['addonAA']['importSSLOrder']['importSSL']['content']                      = 'Importer';
$_LANG['addonAA']['importSSLOrder']['messages']['import_success']                = 'SSL-ordren blev importeret.';
$_LANG['addonAA']['importSSLOrder']['messages']['order_id_not_provided']         = 'API-ordre ID blev ikke angivet.';
$_LANG['addonAA']['importSSLOrder']['messages']['client_id_not_provided']        = 'Klient ID blev ikke angivet.';
$_LANG['addonAA']['importSSLOrder']['messages']['ssl_order_already_exist']       = 'SSL-ordre med det angivne ID eksisterer allerede i systemet.';
$_LANG['addonAA']['importSSLOrder']['messages']['ssl_order_product_not_exist']   = 'Produkt for angivne SSL-ordre ID eksiterer ikke i systemet.';
$_LANG['addonAA']['importSSLOrder']['messages']['order_create_error']            = 'Kunne ikke oprette ordre';
$_LANG['addonAA']['importSSLOrder']['messages']['no_payment_gateway_error']      = 'Der er ikke konfigureret nogen betalingsgateway.';
$_LANG['addonAA']['importSSLOrder']['messages']['order_cancelled_import_unable'] = 'Kunne ikke importere annulleret SSL-ordre.';

$_LANG['addonAA']['userCommissions']['integrationCode']['header']         = 'Integrationskode';
$_LANG['addonAA']['userCommissions']['pleaseNote']                        = 'Bemærk';
$_LANG['addonAA']['userCommissions']['info']                              = 'For at kunne vise produktpriserne med yderligere kommission i klientområdet: ';
$_LANG['addonAA']['userCommissions']['info1']                             = '1. Åben filen';
$_LANG['addonAA']['userCommissions']['info2']                             = '2. Tilføj denne kode til toppen af filen';
$_LANG['addonAA']['userCommissions']['info3']                             = '3. Åben filen';
$_LANG['addonAA']['userCommissions']['info4']                             = '4. Tilføj denne kode til toppen af filen';
$_LANG['addonAA']['pagesLabels']['label']['userCommissions']              = 'Kommissionsregler';
$_LANG['addonAA']['userCommissions']['title']                             = 'Kommissionsregler';
$_LANG['addonAA']['userCommissions']['addNewCommissionRule']              = 'Tilføj ny regel';
$_LANG['addonAA']['userCommissions']['editItem']                          = 'Rediger';
$_LANG['addonAA']['userCommissions']['deleteItem']                        = 'Fjern';
$_LANG['addonAA']['userCommissions']['messages']['addSuccess']            = 'Kommissionsregel tilføjet.';
$_LANG['addonAA']['userCommissions']['messages']['removeSuccess']         = 'Kommissionsregel fjernet.';
$_LANG['addonAA']['userCommissions']['messages']['updateSuccess']         = 'Kommissionsregel opdateret.';
$_LANG['addonAA']['userCommissions']['messages']['clientIDNotProvided']   = 'Klient ID ikke angivet';
$_LANG['addonAA']['userCommissions']['messages']['ruleIDNotProvided']     = 'Regel ID ikke angivet.';
$_LANG['addonAA']['userCommissions']['messages']['productIDNotProvided']  = 'Produkt ID ikke angivet.';
$_LANG['addonAA']['userCommissions']['messages']['commissionNotProvided'] = 'Kommision er ikke angivet.';

$_LANG['addonAA']['userCommissions']['table']['client']                       = 'Klient';
$_LANG['addonAA']['userCommissions']['table']['product']                      = 'Produkt';
$_LANG['addonAA']['userCommissions']['table']['commission']                   = 'Kommission[%]';
$_LANG['addonAA']['userCommissions']['table']['monthly/onetime']              = 'Månedlig/En gang';
$_LANG['addonAA']['userCommissions']['table']['quarterly']                    = 'Kvartalvis';
$_LANG['addonAA']['userCommissions']['table']['semiannually']                 = 'Halvårlig';
$_LANG['addonAA']['userCommissions']['table']['annually']                     = 'Årlig';
$_LANG['addonAA']['userCommissions']['table']['biennially']                   = 'Hvert andet år';
$_LANG['addonAA']['userCommissions']['table']['triennially']                  = 'Hvert tredje år';
$_LANG['addonAA']['userCommissions']['table']['actions']                      = 'Handlinger';
$_LANG['addonAA']['userCommissions']['modal']['pleaseSelecetOnePlaceholder']  = 'Vælg en...';
$_LANG['addonAA']['userCommissions']['modal']['selectClientFirstPlaceholder'] = 'Vælg en klient først...';
$_LANG['addonAA']['userCommissions']['modal']['pleaseSelectProductFirst']     = 'Vælg et produkt først...';
$_LANG['addonAA']['userCommissions']['modal']['noDataAvailable']              = 'Ingen data tilgængelig.';
$_LANG['addonAA']['userCommissions']['modal']['noClientAvailable']            = 'Ingen klient tilgængelig.';
$_LANG['addonAA']['userCommissions']['modal']['noProductAvailable']           = 'Intet produkt tilgængelig.';
$_LANG['addonAA']['userCommissions']['table']['basePrice']                    = 'Basispris: ';
$_LANG['addonAA']['userCommissions']['table']['priceWithCommission']          = 'Med kommission: ';

$_LANG['addonAA']['userCommissions']['modal']['addCommissionRule']          = 'Tilføj ny Kommissionsregel';
$_LANG['addonAA']['userCommissions']['modal']['client']                     = 'Klient';
$_LANG['addonAA']['userCommissions']['modal']['product']                    = 'Produkt';
$_LANG['addonAA']['userCommissions']['modal']['commission']                 = 'Kommission[%]';
$_LANG['addonAA']['userCommissions']['modal']['add']                        = 'Tilføj';
$_LANG['addonAA']['userCommissions']['modal']['edit']                       = 'Gem ændringer';
$_LANG['addonAA']['userCommissions']['modal']['close']                      = 'Luk';
$_LANG['addonAA']['userCommissions']['modal']['productPrice']               = 'Produktpris';
$_LANG['addonAA']['userCommissions']['modal']['productPriceWithCommission'] = 'Produktpris med Kommission';
$_LANG['addonAA']['userCommissions']['modal']['monthly/onetime']            = 'Månedlig/En gang';
$_LANG['addonAA']['userCommissions']['modal']['quarterly']                  = 'Kvartalvis';
$_LANG['addonAA']['userCommissions']['modal']['semiannually']               = 'Halvårlig';
$_LANG['addonAA']['userCommissions']['modal']['annually']                   = 'Årlig';
$_LANG['addonAA']['userCommissions']['modal']['biennially']                 = 'Hvert andet år';
$_LANG['addonAA']['userCommissions']['modal']['triennially']                = 'Hvert tredje år';
$_LANG['addonAA']['userCommissions']['modal']['removeRule']                 = 'Fjern Kommissionsregel';
$_LANG['addonAA']['userCommissions']['modal']['remove']                     = 'Fjern';
$_LANG['addonAA']['userCommissions']['modal']['removeRuleInfo']             = 'Du er ved at fjerne en kommissionsregel. Dette kan ikke fortrydes.';


$_LANG['anErrorOccurred'] = 'Der opstod en fejl';

$_LANG['pagesLabels']['label']['orders']       = 'Importer SSL-Ordre';
$_LANG['addonCA']['sslSummary']['title']       = 'SSL-Ordreoversigt';
$_LANG['addonCA']['sslSummary']['total']       = 'Totale Ordrer';
$_LANG['addonCA']['sslSummary']['unpaid']      = 'Ubetalte Ordrer';
$_LANG['addonCA']['sslSummary']['processing']  = 'Behandler';
$_LANG['addonCA']['sslSummary']['expiresSoon'] = 'Udløber snart';

$_LANG['addonCA']['sslSummaryOrdersPage']['pageTitle']['total']        = 'Totale Ordrer';
$_LANG['addonCA']['sslSummaryOrdersPage']['pageTitle']['unpaid']       = 'Ubetalte Ordrer';
$_LANG['addonCA']['sslSummaryOrdersPage']['pageTitle']['processing']   = 'Behandler';
$_LANG['addonCA']['sslSummaryOrdersPage']['pageTitle']['expires_soon'] = 'Udløber snart';
$_LANG['addonCA']['sslSummaryOrdersPage']['Product/Service']           = 'Produkt/Service';
$_LANG['addonCA']['sslSummaryOrdersPage']['Pricing']                   = 'Pris';
$_LANG['addonCA']['sslSummaryOrdersPage']['Next Due Date']             = 'Næste betalingsdato';
$_LANG['addonCA']['sslSummaryOrdersPage']['Status']                    = 'Status';

$_LANG['invalidEmailAddress']           = 'E-mailadressen er ugyldig';
$_LANG['csrCodeGeneraterdSuccessfully'] = 'CSR-kode blev genereret';
$_LANG['invalidCountryCode']            = 'Landekode er forkert';
$_LANG['csrCodeGeneraterFailed']        = 'Kunne ikke generere CSR-kode';

$_LANG['viewAll']		= 'Vis alle';

$_LANG['additionalSingleDomainInfo'] = 'This product has %s additional single domain SAN\'s included.';
$_LANG['additionalSingleDomainWildcardInfo'] = 'This product has %s additional wildcard domain SAN\'s included.';

$_LANG['addonAA']['userCommissions']['modal']['noClientAvaialblePlaceholder'] = 'No Client Avaialble Placeholder';
$_LANG['addonAA']['userCommissions']['modal']['noProductAvaialblePlaceholder'] = 'No Product Avaialble Placeholder';
$_LANG['addonAA']['userCommissions']['modal']['editCommissionRule'] = 'Edit Commission Rule';
$_LANG['addonAA']['pagesLabels']['label']['logs'] = 'Logs';

$_LANG['addonAA']['logs']['title'] = 'Logs';
$_LANG['addonAA']['logs']['table']['id'] = 'Id';
$_LANG['addonAA']['logs']['table']['client'] = 'Client';
$_LANG['addonAA']['logs']['table']['service'] = 'Service';
$_LANG['addonAA']['logs']['table']['type'] = 'Type';
$_LANG['addonAA']['logs']['table']['msg'] = 'Msg';
$_LANG['addonAA']['logs']['table']['date'] = 'Date';
$_LANG['addonAA']['logs']['table']['actions'] = 'Actions';
$_LANG['addonAA']['logs']['deleteItem'] = 'Delete Log';
$_LANG['addonAA']['pagesLabels']['label']['orders'] = 'Orders';
$_LANG['addonAA']['logs']['modal']['removeLog'] = 'Remove Log';
$_LANG['addonAA']['logs']['modal']['removeLogInfo'] = 'Are you sure remove this log?';
$_LANG['addonAA']['logs']['modal']['remove'] = 'Remove';
$_LANG['addonAA']['logs']['modal']['close'] = 'Close';
$_LANG['addonAA']['logs']['messages']['logIDNotProvided'] = 'Log ID Not Provided';
$_LANG['addonAA']['logs']['button']['clear_logs'] = 'Clear logs';
$_LANG['addonAA']['logs']['modal']['clearLogs'] = 'Clear Logs';
$_LANG['addonAA']['logs']['modal']['clearLogsInfo'] = 'Are you sure you want to clear all logs?';
$_LANG['addonAA']['logs']['modal']['Clear'] = 'Clear';
$_LANG['addonAA']['logs']['messages']['clearSuccess'] = 'All logs have been cleared';
$_LANG['addonAA']['logs']['messages']['removeSuccess'] = 'The log has been successfully deleted';
$_LANG['Reissue Certificate'] = 'Reissue Certificate';
$_LANG['serverCA']['home']['Configuration Submitted'] = 'Configuration Submitted';
$_LANG['serverCA']['home']['anErrorOccurred'] = 'An Error Occurred';

$_LANG['addonAA']['orders']['title'] = 'Orders';
$_LANG['addonAA']['orders']['table']['id'] = 'Id';
$_LANG['addonAA']['orders']['table']['client'] = 'Client';
$_LANG['addonAA']['orders']['table']['service'] = 'Service';
$_LANG['addonAA']['orders']['table']['order'] = 'SSL Order';
$_LANG['addonAA']['orders']['table']['verification_method'] = 'Verification method';
$_LANG['addonAA']['orders']['table']['status'] = 'Status';
$_LANG['addonAA']['orders']['table']['date'] = 'Date';
$_LANG['addonAA']['orders']['table']['actions'] = 'Actions';

$_LANG['Choose a domain'] = 'Choose a domain';

$_LANG['addonAA']['apiConfiguration']['cronCertificateInstaller']['pleaseNote'] = 'Please Note:';
$_LANG['addonAA']['apiConfiguration']['cronCertificateInstaller']['info'] = 'In order to install certificate in cPanel, set the following command line cron (once a day suggested):';
$_LANG['addonAA']['apiConfiguration']['cronCertificateInstaller']['commandLine']['cronFrequency'] = '0 0 * * *';

$_LANG['addonAA']['orders']['table']['set as verified'] = 'Set as verified';
$_LANG['addonAA']['orders']['table']['set as installed'] = 'Set as installed';
$_LANG['addonAA']['orders']['messages']['Success'] = 'Success';