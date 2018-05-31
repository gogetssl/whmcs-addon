<?php

$_LANG['token'] = 'Token';

$_LANG['serverCA']['home']['configurationStatus']                = 'Configuration Status';
$_LANG['serverCA']['home']['Awaiting Configuration']             = 'Awaiting Configuration';
$_LANG['serverCA']['home']['Completed']                          = 'Completed';
$_LANG['serverCA']['home']['configureNow']                       = 'Configure Now';
$_LANG['serverCA']['home']['activationStatus']                   = 'Activation Status';
$_LANG['serverCA']['home']['activationStatusActive']             = 'Active';
$_LANG['serverCA']['home']['activationStatusNewOrder']           = 'Pending';
$_LANG['serverCA']['home']['activationStatusPending']            = 'Pending';
$_LANG['serverCA']['home']['activationStatusCancelled']          = 'Cancelled';
$_LANG['serverCA']['home']['activationStatusPaymentNeeded']      = 'Payment Needed';
$_LANG['serverCA']['home']['activationStatusProcessing']         = 'Processing';
$_LANG['serverCA']['home']['activationStatusIncomplete']         = 'Incomplete';
$_LANG['serverCA']['home']['activationStatusRejected']           = 'Rejected';
$_LANG['serverCA']['home']['validFrom']                          = 'Valid From';
$_LANG['serverCA']['home']['validTill']                          = 'Expires';
$_LANG['serverCA']['home']['domain']                             = 'Domain';
$_LANG['serverCA']['home']['sans']                               = 'SANs';
$_LANG['serverCA']['home']['Partner Order ID']                   = 'Partner Order ID';
$_LANG['serverCA']['home']['ca_chain']                           = 'Intermediate/Chain files';
$_LANG['serverCA']['home']['crt']                                = 'Certificate (CRT)';
$_LANG['serverCA']['home']['csr']                                = 'CSR (Certificate Signing Request)';
$_LANG['serverCA']['home']['activationStatusRejected']           = 'Rejected';
$_LANG['serverCA']['home']['hashFile']                           = 'Hash File';
$_LANG['serverCA']['home']['content']                            = 'Content';
$_LANG['serverCA']['home']['dnsCnameRecord']                     = 'DNS CNAME Record';
$_LANG['serverCA']['home']['validationEmail']                    = 'Validation Email';
$_LANG['serverCA']['home']['resendValidationEmail']              = 'Resend Validation Email';
$_LANG['serverCA']['home']['changeValidationEmail']              = 'Change Validation Email';
$_LANG['serverCA']['home']['revalidate']                         = 'Revalidate';
$_LANG['serverCA']['home']['revalidateModalTitle']               = 'Revalidate';
$_LANG['serverCA']['home']['revalidateModalDomainLabel']         = 'Domain';
$_LANG['serverCA']['home']['revalidateModalMethodLabel']         = 'DCV Method';
$_LANG['serverCA']['home']['revalidateModalEmailLabel']          = 'Email Address';
$_LANG['serverCA']['home']['revalidate']                         = 'Revalidate';
$_LANG['serverCA']['home']['revalidateModalMethodEmail']         = 'EMAIL';
$_LANG['serverCA']['home']['revalidateModalMethodHttp']          = 'HTTP';
$_LANG['serverCA']['home']['revalidateModalMethodHttps']         = 'HTTPS';
$_LANG['serverCA']['home']['revalidateModalMethodDns']           = 'DNS';
$_LANG['serverCA']['home']['loading']                            = 'Loading...';
$_LANG['serverCA']['home']['pleaseChooseOne']                    = 'Please choose one...';
$_LANG['serverCA']['home']['noValidationMethodSelected']         = 'No validation method has been selected for any domain';
$_LANG['serverCA']['home']['noEmailSelectedForDomain']           = 'No email address has been selected for domain: ';
$_LANG['serverCA']['home']['reloadInformation']                  = 'The page will automatically reloaded after 5 seconds.';
$_LANG['serverCA']['home']['changeApproverEmailModalModalTitle'] = 'Change Approver Email';
$_LANG['serverCA']['home']['newApproverEmailModalModalLabel']    = 'New Approver Email';
$_LANG['serverCA']['home']['viewPrivateKeyModalTitle']           = 'View Private Key';
$_LANG['serverCA']['home']['getPrivateKeyBtn']                   = 'Get Private Key';
$_LANG['serverCA']['home']['reissueCertificate']                 = 'Reissue Certificate';
$_LANG['serverCA']['home']['Actions']                            = 'Actions';
$_LANG['serverCA']['home']['Submit']                             = 'Submit';
$_LANG['serverCA']['home']['Close']                              = 'Close';

$_LANG['serverCA']['reissueCertificate'] = 'Reissue Certificate';
$_LANG['serverCA']['contactDetails']     = 'Contact Details';

$_LANG['addonCA']['reissueCertificate'] = $_LANG['serverCA']['reissueCertificate'];
$_LANG['addonCA']['contactDetails']     = $_LANG['serverCA']['contactDetails'];

$_LANG['incorrectCSR']                  = 'Incorrect CSR';
$_LANG['incorrectSans']                 = 'Folowed SAN domains are incorrect: ';
$_LANG['exceededLimitOfSans']           = 'Exceeded limit of SAN domains';
$_LANG['createNotInitialized']          = 'Create has not been initialized';
$_LANG['notAllowToReissue']             = 'Order status not allow to reissue';
$_LANG['canNotFetchWebServer']          = 'Can not fetch Web Server list, plase refresh page or contact support';
$_LANG['canNotGenerateCsrCode']         = 'Can not generate CSR, please refresh page or contact support';
$_LANG['csrCodeGeneraterdSuccessfully'] = 'CSR code has been generated successfully';
$_LANG['csrCodeGeneraterFailed']        = 'Generate CSR code has been failed';
$_LANG['invalidCommonName']             = 'Common Name is incorrect';
$_LANG['invalidEmailAddress']           = 'Email Address is incorrect';
$_LANG['invalidCountryCode']            = 'Country code is incorrect';
$_LANG['orderTypeTitle']                = 'Order Type';
$_LANG['orderTypeLabel']                = 'Type';
$_LANG['newOrder']                      = 'New order';
$_LANG['renewOrder']                    = 'Renewal';
$_LANG['selectOrderTypeDescritpion']    = 'Select Renewal option in case you need renew existing SSL that expires soon. Certification center would add up to 90-days left from Original order.';

$_LANG['reissueOneTitle']        = 'Reissue Certificate';
$_LANG['reissueOneWebServer']    = 'Web Server';
$_LANG['reissueOnePleaseChoose'] = 'Please choose one...';
$_LANG['reissueOneCsr']          = 'CSR';
$_LANG['reissueOneCsr']          = 'CSR';
$_LANG['reissueOneSanDomains']   = 'SAN Domains';

$_LANG['reissueTwoTitle']    = 'Reissue - Certificate Approver Email';
$_LANG['reissueTwoSubTitle'] = 'You must now choose from the options below where you would like the approval email request for this certificate to be sent.';
$_LANG['reissueTwoContinue'] = 'Continue';

$_LANG['reissueThreeSuccess'] = 'Certificate successfully reissued';
$_LANG['mustSelectServer']    = 'You must select your server type';

$_LANG['contact_administrator']      = 'Administrator Contact';
$_LANG['contact_admin_firstname']    = 'First Name';
$_LANG['contact_admin_lastname']     = 'Last Name';
$_LANG['contact_admin_organization'] = 'Organization';
$_LANG['contact_admin_title']        = 'Job Title';
$_LANG['contact_admin_addressline1'] = 'Address';
$_LANG['contact_admin_city']         = 'City';
$_LANG['contact_admin_country']      = 'Country';
$_LANG['contact_admin_postalcode']   = 'Zip Code';
$_LANG['contact_admin_region']       = 'Region';
$_LANG['contact_admin_phone']        = 'Phone';
$_LANG['contact_admin_fax']          = 'Fax';
$_LANG['contact_admin_email']        = 'Email';

$_LANG['contact_technical']         = 'Technical Contact';
$_LANG['contact_tech_firstname']    = 'Firstname';
$_LANG['contact_tech_lastname']     = 'Lastname';
$_LANG['contact_tech_organization'] = 'Organization';
$_LANG['contact_tech_title']        = 'Job Title';
$_LANG['contact_tech_city']         = 'City';
$_LANG['contact_tech_country']      = 'Country';
$_LANG['contact_tech_postalcode']   = 'Zip Code';
$_LANG['contact_tech_region']       = 'Region';
$_LANG['contact_tech_phone']        = 'Phone';
$_LANG['contact_tech_fax']          = 'Fax';
$_LANG['contact_tech_email']        = 'Email';

$_LANG['create_not_initialized'] = ' Create has not been initialized';

$_LANG['sansTitle']                   = 'SANs';
$_LANG['sansDescription']             = 'If you want add any SANs put them here (every SAN in separate line)';
$_LANG['sansFreindlyName']            = 'SAN Domains';
$_LANG['confOrganizationTitle']       = 'Organization Contact Information';
$_LANG['confOrganizationName']        = 'Organization Name';
$_LANG['confOrganizationDivision']    = 'Division';
$_LANG['confOrganizationDuns']        = 'Duns';
$_LANG['confOrganizationAddress']     = 'Address';
$_LANG['confOrganizationCity']        = 'City';
$_LANG['confOrganizationCountry']     = 'Country';
$_LANG['confOrganizationFax']         = 'Fax';
$_LANG['confOrganizationPhoneNumber'] = 'Phone Number';
$_LANG['confOrganizationZipCode']     = 'Zip Code';
$_LANG['confOrganizationStateRegion'] = 'State / Region';

$_LANG['anErrorOccurred'] = 'An error occurred';

$_LANG['stepTwoTableLabelDomain']       = 'Domain';
$_LANG['stepTwoTableLabelDcvMethod']    = 'DCV Method';
$_LANG['stepTwoTableLabelEmail']        = 'Email Address';
$_LANG['dropdownDcvMethodEmail']        = 'EMAIL';
$_LANG['dropdownDcvMethodHttp']         = 'HTTP';
$_LANG['dropdownDcvMethodHttps']        = 'HTTPS';
$_LANG['dropdownDcvMethodDns']          = 'DNS';
$_LANG['generateCsrModalTitle']         = 'Generate CSR';
$_LANG['countryLabel']                  = 'Country';
$_LANG['stateLabel']                    = 'State';
$_LANG['localityLabel']                 = 'Locality';
$_LANG['organizationLabel']             = 'Organization';
$_LANG['organizationanUnitLabel']       = 'Organization Unit';
$_LANG['commonNameLabel']               = 'Common Name';
$_LANG['emailAddressLabel']             = 'Email Address';
$_LANG['statePlaceholder']              = 'Texas';
$_LANG['localityPlaceholder']           = 'San Antonio';
$_LANG['organizationPlaceholder']       = 'Big Bobs Beepers';
$_LANG['organizationanUnitPlaceholder'] = 'Marketing';
$_LANG['commonNamePlaceholder']         = 'example.com';
$_LANG['emailAddressPlaceholder']       = 'example@example.com';
$_LANG['Generate CSR']                  = 'Generate CSR';
$_LANG['Submit']                        = 'Submit';
$_LANG['Close']                         = 'Close';
$_LANG['Please choose one...']          = 'Please choose one...';

$_LANG['adminJobTitleMissing']    = 'You did not enter Administrative Job Title.';
$_LANG['organizationNameMissing'] = 'You did not enter Organization Name.';
$_LANG['orderTypeMissing']        = 'You did not select Order Type.';
$_LANG['incorrectCSR']            = 'Incorrect CSR';
$_LANG['sanLimitExceeded']        = 'Exceeded limit of SAN domains';
$_LANG['incorrectSans']           = 'Folowed SAN domains are incorrect: ';

