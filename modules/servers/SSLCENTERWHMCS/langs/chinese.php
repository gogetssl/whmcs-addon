<?php

$_LANG['token'] = 'Token';

$_LANG['serverCA']['home']['configurationStatus']                = '配置状态';
$_LANG['serverCA']['home']['custom_guide']                       = '帮助';
$_LANG['serverCA']['home']['Awaiting Configuration']             = '等待配置';
$_LANG['serverCA']['home']['Completed']                          = '已完成';
$_LANG['serverCA']['home']['configureNow']                       = '正在配置';
$_LANG['serverCA']['home']['activationStatus']                   = 'SSL 状态';
$_LANG['serverCA']['home']['activationStatusActive']             = '正常';
$_LANG['serverCA']['home']['activationStatusNewOrder']           = '等待提交 CSR';
$_LANG['serverCA']['home']['activationStatusPending']            = '等待提交 CSR';
$_LANG['serverCA']['home']['activationStatusCancelled']          = '已取消';
$_LANG['serverCA']['home']['activationStatusPaymentNeeded']      = '等待付款';
$_LANG['serverCA']['home']['activationStatusProcessing']         = '处理中';
$_LANG['serverCA']['home']['activationStatusIncomplete']         = '未完成';
$_LANG['serverCA']['home']['activationStatusRejected']           = '被拒绝';
$_LANG['serverCA']['home']['validFrom']                          = '有效期自';
$_LANG['serverCA']['home']['validTill']                          = '至';
$_LANG['serverCA']['home']['domain']                             = '域名';
$_LANG['serverCA']['home']['sans']                               = '备用名称';
$_LANG['serverCA']['home']['Partner Order ID']                   = '合作伙伴订单 ID';
$_LANG['serverCA']['home']['ca_chain']                           = '证书链文件';
$_LANG['serverCA']['home']['crt']                                = '证书文件 (CRT)';
$_LANG['serverCA']['home']['csr']                                = 'CSR (Certificate Signing Request，证书签名请求)';
$_LANG['serverCA']['home']['activationStatusRejected']           = '申请被拒绝';
$_LANG['serverCA']['home']['hashFile']                           = 'Hash 文件';
$_LANG['serverCA']['home']['content']                            = '联系方式';
$_LANG['serverCA']['home']['dnsCnameRecord']                     = 'DNS CNAME 记录';
$_LANG['serverCA']['home']['validationEmail']                    = '验证邮件';
$_LANG['serverCA']['home']['renew']                              = '续期';
$_LANG['serverCA']['home']['renewModalTitle']                    = '为证书续期';
$_LANG['serverCA']['home']['renewModalConfirmInformation']       = '您将要续订证书，是否继续？';
$_LANG['serverCA']['home']['redirectToInvoiceInformation']       = '5秒钟后，您将自动重定向到订单页面。';
$_LANG['serverCA']['home']['resendValidationEmail']              = '重新发送验证邮件';
$_LANG['serverCA']['home']['changeValidationEmail']              = '修改验证邮件地址';
$_LANG['serverCA']['home']['domainvalidationmethod']             = '域名验证方式';
$_LANG['serverCA']['home']['revalidate']                         = '重新验证';
$_LANG['serverCA']['home']['download']                           = '下载验证文件';
$_LANG['serverCA']['home']['revalidateModalTitle']               = '域名验证方式';
$_LANG['serverCA']['home']['revalidateModalDomainLabel']         = '域名';
$_LANG['serverCA']['home']['revalidateModalMethodLabel']         = '域名所有权验证';
$_LANG['serverCA']['home']['revalidateModalEmailLabel']          = 'Email 地址验证';
$_LANG['serverCA']['home']['revalidateModalMethodEmail']         = 'Email 地址';
$_LANG['serverCA']['home']['revalidateModalMethodHttp']          = 'HTTP 验证';
$_LANG['serverCA']['home']['revalidateModalMethodHttps']         = 'HTTPS 验证';
$_LANG['serverCA']['home']['revalidateModalMethodDns']           = 'DNS 记录验证';
$_LANG['serverCA']['home']['loading']                            = '加载中……';
$_LANG['serverCA']['home']['pleaseChooseOne']                    = '请选择一种验证方式';
$_LANG['serverCA']['home']['noValidationMethodSelected']         = '您没有为任何域名选择验证方法';
$_LANG['serverCA']['home']['noEmailSelectedForDomain']           = '没有为以下域名配置用于批准证书的 Email 地址：';
$_LANG['serverCA']['home']['reloadInformation']                  = '此页将在5秒后自动重载。';
$_LANG['serverCA']['home']['changeApproverEmailModalModalTitle'] = '更改用于验证的 Email 地址';
$_LANG['serverCA']['home']['newApproverEmailModalModalLabel']    = '新增用于验证的 Email 地址';
$_LANG['serverCA']['home']['viewPrivateKeyModalTitle']           = '查看私钥';
$_LANG['serverCA']['home']['getPrivateKeyBtn']                   = '获取私钥';
$_LANG['serverCA']['home']['recheckCertificateDetails']          = '查看证书详细信息'; 
$_LANG['serverCA']['home']['reissueCertificate']                 = '重新签发证书';
$_LANG['serverCA']['home']['Actions']                            = '操作';
$_LANG['serverCA']['home']['Submit']                             = '提交';
$_LANG['serverCA']['home']['Close']                              = '关闭';
$_LANG['serverCA']['home']['sendCertificate']                    = '发送证书';
$_LANG['serverCA']['home']['downloadca']                         = '下载证书链';
$_LANG['serverCA']['home']['downloadcrt']                        = '下载证书';
$_LANG['serverCA']['home']['downloadcsr']                        = '下载证书签名请求（CSR）';
$_LANG['serverCA']['home']['orderNotActiveError']                = '无法发送证书：证书状态异常。';
$_LANG['serverCA']['home']['CACodeEmptyError']                   = '发生错误：证书内容为空。';
$_LANG['serverCA']['home']['sendCertificateSuccess']             = '证书文件已成功发送。';

$_LANG['serverCA']['reissueCertificate'] = '重新签发证书';
$_LANG['serverCA']['contactDetails']     = '详细联系方式';

$_LANG['addonCA']['reissueCertificate'] = $_LANG['serverCA']['reissueCertificate'];
$_LANG['addonCA']['contactDetails']     = $_LANG['serverCA']['contactDetails'];

$_LANG['incorrectCSR']                  = '错误的证书签名请求（CSR）';
$_LANG['incorrectSans']                 = '以下备用名称格式错误：';
$_LANG['exceededLimitOfSans']           = '备用名称数量超过限制';
$_LANG['createNotInitialized']          = '证书创建尚未初始化，请稍候';
$_LANG['notAllowToReissue']             = '订单状态异常，不允许重新签发';
$_LANG['canNotFetchWebServer']          = '无法获取 Web 服务器列表，请刷新页面或联系技术支持';
$_LANG['canNotGenerateCsrCode']         = '无法生成证书签名请求（CSR），请刷新页面或联系技术支持';
$_LANG['csrCodeGeneraterdSuccessfully'] = 'CSR 代码创建成功';
$_LANG['csrCodeGeneraterFailed']        = 'CSR 代码创建失败';
$_LANG['invalidCommonName']             = '通用名称（Common Name, CN)格式错误';
$_LANG['invalidEmailAddress']           = 'Email 地址格式错误';
$_LANG['invalidCountryCode']            = '国家代码（Country Code）错误';
$_LANG['orderTypeTitle']                = '订单类型';
$_LANG['orderTypeLabel']                = '类型';
$_LANG['newOrder']                      = '新订单';
$_LANG['renewOrder']                    = '续费';
$_LANG['selectOrderTypeDescritpion']    = '如果需要续订即将到期的现有SSL，请选择续订选项。续订证书后，新签发的证书有效期最多额外增加90天。';

$_LANG['reissueOneTitle']        = '重新签发证书';
$_LANG['reissueOneWebServer']    = 'Web 服务器';
$_LANG['reissueOnePleaseChoose'] = '请选择一种验证方式';
$_LANG['reissueOneCsr']          = 'CSR (Certificate Signing Request，证书签名请求)';
$_LANG['reissueOneCsr']          = 'CSR (Certificate Signing Request，证书签名请求)';
$_LANG['reissueOneSanDomains']   = '备用名称';

$_LANG['reissueTwoTitle']                            = '重新签发：通过 Email 地址验证';
$_LANG['reissueSelectVerificationMethodTitle']       = '重新签发：通过域名所有权验证';
$_LANG['reissueTwoSubTitle']                         = '请在以下 Email 地址中选择一个用于进行批准证书签发';
$_LANG['reissueSelectVerificationMethodDescription'] = '请在以下域名所有权验证方式中选择一种用于验证';
$_LANG['reissueTwoContinue']                         = '继续';

$_LANG['reissueThreeSuccess'] = '证书重新签发成功';
$_LANG['mustSelectServer']    = '请选择您的服务器类型';

$_LANG['contact_administrator']      = '管理员联系方式';
$_LANG['contact_admin_firstname']    = '名字（First Name）';
$_LANG['contact_admin_lastname']     = '姓氏（Last Name）';
$_LANG['contact_admin_organization'] = '单位名称';
$_LANG['contact_admin_title']        = '职位/职务';
$_LANG['contact_admin_addressline1'] = '地址';
$_LANG['contact_admin_city']         = '城市';
$_LANG['contact_admin_country']      = '国家';
$_LANG['contact_admin_postalcode']   = '邮政编码';
$_LANG['contact_admin_region']       = '州/地区';
$_LANG['contact_admin_phone']        = '电话号码';
$_LANG['contact_admin_fax']          = '传真号码';
$_LANG['contact_admin_email']        = 'Email 地址';

$_LANG['contact_technical']      = '技术人员联系方式';
$_LANG['contact_tech_firstname']    = '名字（First Name）';
$_LANG['contact_tech_lastname']     = '姓氏（Last Name）';
$_LANG['contact_tech_organization'] = '单位名称';
$_LANG['contact_tech_title']        = '职位/职务';
$_LANG['contact_tech_addressline1'] = '地址';
$_LANG['contact_tech_city']         = '城市';
$_LANG['contact_tech_country']      = '国家';
$_LANG['contact_tech_postalcode']   = '邮政编码';
$_LANG['contact_tech_region']       = '州/地区';
$_LANG['contact_tech_phone']        = '电话号码';
$_LANG['contact_tech_fax']          = '传真号码';
$_LANG['contact_tech_email']        = 'Email 地址';

$_LANG['create_not_initialized'] = '证书创建尚未初始化，请稍候';

$_LANG['sansTitle']                   = '备用名称';
$_LANG['sansDescription']             = '请在下方输入每一条需要的备用域名';
$_LANG['sansFreindlyName']            = '备用域名';
$_LANG['confOrganizationTitle']       = '组织/单位联系信息';
$_LANG['confOrganizationName']        = '单位名称';
$_LANG['confOrganizationDivision']    = '部门';
$_LANG['confOrganizationDuns']        = '单位编号（邓白氏编码等）';
$_LANG['confOrganizationAddress']     = '地址';
$_LANG['confOrganizationCity']        = '城市';
$_LANG['confOrganizationCountry']     = '国家';
$_LANG['confOrganizationFax']         = '传真号码';
$_LANG['confOrganizationPhoneNumber'] = '电话号码';
$_LANG['confOrganizationZipCode']     = '邮政编码';
$_LANG['confOrganizationStateRegion'] = '州/地区';

$_LANG['anErrorOccurred'] = '出现错误';

$_LANG['sslcertSelectVerificationMethodTitle']       = '证书验证';
$_LANG['sslcertSelectVerificationMethodDescription'] = '请从以下方式中选择一种以进行域名所有权验证。';
$_LANG['stepTwoTableLabelDomain']                    = '域名';
$_LANG['stepTwoTableLabelDcvMethod']                 = '域名所有权验证';
$_LANG['stepTwoTableLabelEmail']                     = 'Email 地址验证';
$_LANG['dropdownDcvMethodEmail']                     = 'Email 地址';
$_LANG['dropdownDcvMethodHttp']                      = 'HTTP 验证';
$_LANG['dropdownDcvMethodHttps']                     = 'HTTPS 验证';
$_LANG['dropdownDcvMethodDns']                       = 'DNS 记录验证';
$_LANG['generateCsrModalTitle']                      = '创建 CSR';
$_LANG['countryLabel']                               = '国家';
$_LANG['stateLabel']                                 = '州/地区';
$_LANG['localityLabel']                              = '城市';
$_LANG['organizationLabel']                          = '单位名称';
$_LANG['organizationanUnitLabel']                    = '部门';
$_LANG['commonNameLabel']                            = '通用名称（Common Name）';
$_LANG['emailAddressLabel']                          = 'Email 地址';
$_LANG['statePlaceholder']                           = 'Texas';
$_LANG['localityPlaceholder']                        = 'San Antonio';
$_LANG['organizationPlaceholder']                    = 'Big Bobs Beepers';
$_LANG['organizationanUnitPlaceholder']              = 'Marketing';
$_LANG['commonNamePlaceholder']                      = 'example.com';
$_LANG['commonNamePlaceholderWildCard']              = '*.domain.tld (requires)';
$_LANG['emailAddressPlaceholder']                    = 'example@example.com';
$_LANG['Generate CSR']                               = '创建 CSR';
$_LANG['Submit']                                     = '提交';
$_LANG['Close']                                      = '关闭';
$_LANG['Please choose one...']                       = '请选择一种验证方式';

$_LANG['adminJobTitleMissing']    = '未输入管理员职务';
$_LANG['organizationNameMissing'] = '未输入单位名称';
$_LANG['orderTypeMissing']        = '未选择订单类型';
$_LANG['incorrectCSR']            = 'CSR 格式错误';
$_LANG['sanLimitExceeded']        = '备用名称数量超过限制';
$_LANG['incorrectSans']           = '以下备用名称格式错误：';

$_LANG['addonCA']['sslSummary']['title']           = 'SSL 订单摘要';
$_LANG['addonCA']['sslSummary']['total']           = '总订单';
$_LANG['addonCA']['sslSummary']['unpaid']          = '未支付订单';
$_LANG['addonCA']['sslSummary']['processing']      = '处理中';
$_LANG['addonCA']['sslSummary']['expiresSoon']     = '即将过期';
$_LANG['addonCA']['customBackToServiceButtonLang'] = '« 返回服务列表';
