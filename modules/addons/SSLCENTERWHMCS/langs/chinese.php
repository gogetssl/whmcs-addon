<?php

$_LANG['token']        = ', Token 错误:';
$_LANG['generalError'] = '出现错误，请查阅日志并联系管理员。';

//SSLCENTER configuration SSLCENTER 配置
$_LANG['addonAA']['pagesLabels']['label']['apiConfiguration']                                          = 'API 配置';
$_LANG['addonAA']['apiConfiguration']['crons']['header']                                               = '计划任务';
//synchronization cron 有关同步的计划任务
$_LANG['addonAA']['apiConfiguration']['DailyCron']['pleaseNote']                             = '请注意：';
$_LANG['addonAA']['apiConfiguration']['DailyCron']['info']                                   = '为了启用每日自动同步，请设置以下 cron 命令行：【建议每日执行一次】';
$_LANG['addonAA']['apiConfiguration']['DailyCron']['commandLine']['cronFrequency']           = '0 0 * * *';
//processing cron 有关订单处理的计划任务
$_LANG['addonAA']['apiConfiguration']['cronProcessing']['pleaseNote']                             = '请注意：';
$_LANG['addonAA']['apiConfiguration']['cronProcessing']['info']                                   = '为了启用自动同步处理订单，请设置以下 cron 命令行：【建议每5分钟执行一次】';
$_LANG['addonAA']['apiConfiguration']['cronProcessing']['commandLine']['cronFrequency']           = '*/5 * * * *';

//synchronization cron 有关同步的计划任务
$_LANG['addonAA']['apiConfiguration']['cronSynchronization']['pleaseNote']                             = '请注意：';
$_LANG['addonAA']['apiConfiguration']['cronSynchronization']['info']                                   = '为了启用自动同步，请设置以下 cron 命令行：【建议每小时执行一次】';
$_LANG['addonAA']['apiConfiguration']['cronSynchronization']['commandLine']['cronFrequency']           = '0 */1 * * *';
//summary order cron 有关订单摘要的计划任务
$_LANG['addonAA']['apiConfiguration']['cronSSLSummaryStats']['pleaseNote']                             = '请注意：';
$_LANG['addonAA']['apiConfiguration']['cronSSLSummaryStats']['info']                                   = '为了加载当前 SSL 订单状态，请设置以下 cron 命令行：【建议每4小时执行一次】';
$_LANG['addonAA']['apiConfiguration']['cronSSLSummaryStats']['commandLine']['cronFrequency']           = '1 */4 * * *';
//customers notification and creating renewals 有关客户通知和续订的计划任务
$_LANG['addonAA']['apiConfiguration']['cronRenewal']['pleaseNote']                                     = '请注意：';
$_LANG['addonAA']['apiConfiguration']['cronRenewal']['info']                                           = '为了向客户发送有关服务到期的通知并为在设定的天数内到期的服务创建续费订单，请设置以下 cron 命令行：【建议每天执行一次】';
$_LANG['addonAA']['apiConfiguration']['cronRenewal']['commandLine']['cronFrequency']                   = '0 0 * * *';
//customers send certificate 有关向客户发送证书的计划任务
$_LANG['addonAA']['apiConfiguration']['cronSendCertificate']['pleaseNote']                             = '请注意：';
$_LANG['addonAA']['apiConfiguration']['cronSendCertificate']['info']                                   = '为了在 SSL 证书签发成功时向客户发送证书，请设置以下 cron 命令行：【建议每3小时执行一次】';
$_LANG['addonAA']['apiConfiguration']['cronSendCertificate']['commandLine']['cronFrequency']           = '0 3 * * *';
//customers send certificate 有关向客户发送证书的计划任务
$_LANG['addonAA']['apiConfiguration']['cronPriceUpdater']['pleaseNote']                             = '请注意：';
$_LANG['addonAA']['apiConfiguration']['cronPriceUpdater']['info']                                   = '为了使 WHMCS 产品价格与 API 产品价格同步，请设置以下 cron 命令行：【建议每3天执行一次】';
$_LANG['addonAA']['apiConfiguration']['cronPriceUpdater']['commandLine']['cronFrequency']           = '0 0 */3 * *';
//customers send certificate 有关向客户发送证书的计划任务
$_LANG['addonAA']['apiConfiguration']['cronCertificateDetailsUpdater']['pleaseNote']                             = '请注意：';
$_LANG['addonAA']['apiConfiguration']['cronCertificateDetailsUpdater']['info']                                   = '为了使 WHMCS 中的证书详细信息与 API 中的证书详细信息同步，请设置以下 cron 命令行：【建议每天执行一次】';
$_LANG['addonAA']['apiConfiguration']['cronCertificateDetailsUpdater']['commandLine']['cronFrequency']           = '0 0 * * *';
//
$_LANG['addonAA']['apiConfiguration']['item']['header']                                                = 'API 配置';
$_LANG['addonAA']['apiConfiguration']['item']['api_login']['label']                                    = '用户名';
$_LANG['addonAA']['apiConfiguration']['item']['api_password']['label']                                 = '密码';
$_LANG['addonAA']['apiConfiguration']['item']['tech_legend']['label']                                  = '技术人员联系方式';
$_LANG['addonAA']['apiConfiguration']['item']['csr_generator_legend']['label']                         = 'CSR 生成器';
$_LANG['addonAA']['apiConfiguration']['item']['display_csr_generator']['label']                        = '允许使用 CSR 生成器';
$_LANG['addonAA']['apiConfiguration']['item']['default_csr_generator_country']['description']          = 'CSR 生成器默认国家选项';
$_LANG['addonAA']['apiConfiguration']['item']['display_ca_summary']['label']                           = '显示订单摘要';
$_LANG['addonAA']['apiConfiguration']['item']['client_area_summary_orders']['label']                   = '客户中心订单摘要';
$_LANG['addonAA']['apiConfiguration']['item']['validation_settings']['label']                          = '验证设定';
$_LANG['addonAA']['apiConfiguration']['item']['disable_email_validation']['label']                     = '禁用电子邮件验证';
$_LANG['addonAA']['apiConfiguration']['item']['summary_expires_soon_days']['label']                    = '过期前的天数';
$_LANG['addonAA']['apiConfiguration']['item']['summary_expires_soon_days']['description']              = '如果 SSL 订单的到期日在上述日期当天或之前，则在统计中对其计数';
$_LANG['addonAA']['apiConfiguration']['item']['send_certificate_template']['label']                    = '向客户发送证书时使用的电子邮件模板';
$_LANG['addonAA']['apiConfiguration']['item']['send_certificate_template']['description']              = '若要通过所选模板发送 SSL 证书，请对其进行编辑并将 {$ssl_certyficate} 字段置入其中。';
//
$_LANG['addonAA']['apiConfiguration']['item']['data_migration_legend']['label']                        = '数据和设定迁移配置';
$_LANG['addonAA']['apiConfiguration']['item']['data_migration']['content']                             = '迁移';
$_LANG['addonAA']['apiConfiguration']['modal']['import']                                               = '迁移';
$_LANG['addonAA']['apiConfiguration']['modal']['close']                                                = '关闭';
$_LANG['addonAA']['apiConfiguration']['modal']['migrationData']                                        = '导入数据和配置';
$_LANG['addonAA']['apiConfiguration']['migrationOldModuleDataExixts']                                  = '以下是与 GoGetSSL WHMCS 模块关联的产品或服务：';
$_LANG['addonAA']['apiConfiguration']['migrationProductIDs']                                           = '产品 ID：';
$_LANG['addonAA']['apiConfiguration']['migrationServiceIDs']                                           = '服务 ID：';
$_LANG['addonAA']['apiConfiguration']['migrationPerformMigration']                                     = '执行“数据迁移”以使数据和配置与 SSLCENTER WHMCS 模块相关联。';
$_LANG['addonAA']['apiConfiguration']['modal']['dataMigrationInfo']                                    = '您将要从 GoGetSSL WHMCS 模块迁移数据和配置，【此过程不可逆】。';
$_LANG['addonAA']['apiConfiguration']['modal']['dataMigrationInfo2']                                   = '即将执行的操作：';
$_LANG['addonAA']['apiConfiguration']['modal']['dataMigrationInfoAction'][0]                           = '导入插件配置';
$_LANG['addonAA']['apiConfiguration']['modal']['dataMigrationInfoAction'][1]                           = '更新现有产品（更改分配的模块）';
$_LANG['addonAA']['apiConfiguration']['modal']['dataMigrationInfoAction'][2]                           = '更新现有服务（更改分配的模块）';
$_LANG['addonAA']['apiConfiguration']['messages']['data_migration_success']                            = '数据和配置已成功导入……本页面将在 5 秒钟后自动重新加载';
//
$_LANG['addonAA']['apiConfiguration']['item']['renewal_settings_legend']['label']                      = '续订设置';
$_LANG['addonAA']['apiConfiguration']['item']['logs_settings_legend']['label']                      = '日志设置';
$_LANG['addonAA']['apiConfiguration']['item']['auto_renew_invoice_reccuring']['label']                 = '循环订单';
$_LANG['addonAA']['apiConfiguration']['item']['auto_renew_invoice_reccuring']['description']           = '创建自动续费订单';
$_LANG['addonAA']['apiConfiguration']['item']['send_expiration_notification_reccuring']['label']       = '';
$_LANG['addonAA']['apiConfiguration']['item']['renew_invoice_days_reccuring']['description']           = '剩余有效期';
$_LANG['addonAA']['apiConfiguration']['item']['send_expiration_notification_reccuring']['description'] = '发送过期通知';
$_LANG['addonAA']['apiConfiguration']['item']['auto_renew_invoice_one_time']['label']                  = '一次性订单';
$_LANG['addonAA']['apiConfiguration']['item']['auto_renew_invoice_one_time']['description']            = '创建自动续费订单';
$_LANG['addonAA']['apiConfiguration']['item']['send_expiration_notification_one_time']['label']        = '';
$_LANG['addonAA']['apiConfiguration']['item']['renew_invoice_days_one_time']['description']            = '剩余有效期';
$_LANG['addonAA']['apiConfiguration']['item']['send_expiration_notification_one_time']['description']  = '发送过期通知';

$_LANG['addonAA']['apiConfiguration']['item']['automatic_processing_of_renewal_orders']['label']                  = '';
$_LANG['addonAA']['apiConfiguration']['item']['automatic_processing_of_renewal_orders']['description']            = '自动处理续费订单';
$_LANG['addonAA']['apiConfiguration']['item']['sidebar_templates']['label']                  = '侧边栏页面列表';
$_LANG['addonAA']['apiConfiguration']['item']['sidebar_templates']['description']            = '输入页面列表（用逗号分隔）。如果将侧边栏留空，则在每个页面上都将可见。';
$_LANG['addonAA']['apiConfiguration']['item']['custom_guide']['label']                  = '模板内容';
$_LANG['addonAA']['apiConfiguration']['item']['custom_guide']['description']            = '';
$_LANG['addonAA']['apiConfiguration']['item']['renew_new_order']['label']                  = '';
$_LANG['addonAA']['apiConfiguration']['item']['renew_new_order']['description']            = '通过现有订单续费';
$_LANG['addonAA']['apiConfiguration']['item']['visible_renew_button']['label']                  = '';
$_LANG['addonAA']['apiConfiguration']['item']['visible_renew_button']['description']            = '在客户中心显示“续订”按钮';
$_LANG['addonAA']['apiConfiguration']['item']['save_activity_logs']['label']                  = '活动日志';
$_LANG['addonAA']['apiConfiguration']['item']['save_activity_logs']['description']            = '勾选复选框以保存日志';
//
$_LANG['addonAA']['apiConfiguration']['item']['tech_firstname']['label']                               = '名字（First Name）';
$_LANG['addonAA']['apiConfiguration']['item']['use_admin_contact']['label']                            = '使用管理员联系方式';
$_LANG['addonAA']['apiConfiguration']['item']['tech_lastname']['label']                                = '姓氏（Last Name）';
$_LANG['addonAA']['apiConfiguration']['item']['tech_organization']['label']                            = '单位名称';
$_LANG['addonAA']['apiConfiguration']['item']['tech_title']['label']                                   = '职位/职务';
$_LANG['addonAA']['apiConfiguration']['item']['tech_addressline1']['label']                            = '地址';
$_LANG['addonAA']['apiConfiguration']['item']['tech_phone']['label']                                   = '电话号码';
$_LANG['addonAA']['apiConfiguration']['item']['tech_email']['label']                                   = 'Email 地址';
$_LANG['addonAA']['apiConfiguration']['item']['tech_city']['label']                                    = '城市';
$_LANG['addonAA']['apiConfiguration']['item']['tech_country']['label']                                 = '国家';
$_LANG['addonAA']['apiConfiguration']['item']['tech_fax']['label']                                     = '传真号码';
$_LANG['addonAA']['apiConfiguration']['item']['tech_postalcode']['label']                              = '邮政编码';
$_LANG['addonAA']['apiConfiguration']['item']['tech_region']['label']                                  = '州/地区';

$_LANG['addonAA']['apiConfiguration']['item']['testConnection']['content'] = '测试本服务器（WHMCS）到 GoGetSSL API 服务器的连接';
$_LANG['addonAA']['apiConfiguration']['item']['saveItem']['label']         = '保存';
$_LANG['addonAA']['pagesLabels']['label']['productsConfiguration']         = '产品设置';
$_LANG['addonAA']['pagesLabels']['label']['productsCreator']               = '创建产品';
$_LANG['addonAA']['pagesLabels']['apiConfiguration']['saveItem']           = '保存';

$_LANG['addonAA']['apiConfiguration']['messages']['api_connection_success'] = '连接已建立。';


$_LANG['addonAA']['productsConfiguration']['sslCenterProduct']    = 'SSLCenter 产品：';
$_LANG['addonAA']['productsConfiguration']['productName']         = '产品名称';
$_LANG['addonAA']['productsConfiguration']['customguide']         = '自定义产品说明：（支持 HTML 格式）';
$_LANG['addonAA']['productsConfiguration']['configurableOptions'] = '可配置选项：';
$_LANG['addonAA']['productsConfiguration']['createConfOptions']   = '生成';
$_LANG['addonAA']['productsConfiguration']['editPrices']          = '编辑价格';
$_LANG['addonAA']['productsConfiguration']['autoSetup']           = '开通方式：';
$_LANG['addonAA']['productsConfiguration']['autoSetupOrder']      = '当客户下单之后（未付款）立即自动开通';
$_LANG['addonAA']['productsConfiguration']['autoSetupPayment']    = '当收到客户首付款时自动开通';
$_LANG['addonAA']['productsConfiguration']['autoSetupOn']         = '手动审核通过后自动开通';
$_LANG['addonAA']['productsConfiguration']['autoSetupOff']        = '手动开通';
$_LANG['addonAA']['productsConfiguration']['months']              = '最大月份：';
$_LANG['addonAA']['productsConfiguration']['enableSans']          = '启用子域名：';
$_LANG['addonAA']['productsConfiguration']['includedSans']        = '包括子域名：';
$_LANG['addonAA']['productsConfiguration']['status']              = '状态：';
$_LANG['addonAA']['productsConfiguration']['setForManyProducts']  = '为多个产品设置';
$_LANG['addonAA']['productsConfiguration']['statusEnabled']       = '已启用的产品';
$_LANG['addonAA']['productsConfiguration']['allOrSelectedProducts'] = '所有或已选定的产品：';
$_LANG['addonAA']['productsConfiguration']['selectProducts']      = '选择产品：';
$_LANG['addonAA']['productsConfiguration']['allProducts']         = '所有产品';
$_LANG['addonAA']['productsConfiguration']['selectedProducts']    = '已选定的产品';
$_LANG['addonAA']['productsConfiguration']['areYouSureManyProducts'] = '确定要对多个产品使用这些设置吗？';
$_LANG['addonAA']['productsConfiguration']['doNotAnything']       = '不自动开通';

$_LANG['addonAA']['productsConfiguration']['statusEnable']  = '启用';
$_LANG['addonAA']['productsConfiguration']['statusDisable'] = '禁用';


$_LANG['addonAA']['productsConfiguration']['paymentType']          = '付款方式：';
$_LANG['addonAA']['productsConfiguration']['priceAutoDownlaod']    = '自动下载价格：';
$_LANG['addonAA']['productsConfiguration']['commission']           = '折扣[%]:';
$_LANG['addonAA']['productsConfiguration']['paymentTypeFree']      = '免费';
$_LANG['addonAA']['productsConfiguration']['paymentTypeRecurring'] = '循环';
$_LANG['addonAA']['productsConfiguration']['paymentTypeOneTime']   = '一次性';

$_LANG['addonAA']['productsConfiguration']['pricing']             = '定价：';
$_LANG['addonAA']['productsConfiguration']['pricingMonthly']      = '月付/一次性';
$_LANG['addonAA']['productsConfiguration']['pricingQuarterly']    = '季付';
$_LANG['addonAA']['productsConfiguration']['pricingSemiAnnually'] = '半年';
$_LANG['addonAA']['productsConfiguration']['pricingAnnually']     = '年付';
$_LANG['addonAA']['productsConfiguration']['pricingBiennially']   = '两年';
$_LANG['addonAA']['productsConfiguration']['pricingTriennially']  = '三年';

$_LANG['addonAA']['productsConfiguration']['pricingSetupFee']        = '初装费';
$_LANG['addonAA']['productsConfiguration']['pricingPrice']           = '价格';
$_LANG['addonAA']['productsConfiguration']['pricingCommissionPrice'] = '价格（折扣后）';
$_LANG['addonAA']['productsConfiguration']['pricingEnable']          = '启用';

$_LANG['addonAA']['productsConfiguration']['save']         = '保存';
$_LANG['addonAA']['productsConfiguration']['messages'][''] = '';


$_LANG['addonAA']['productsCreator']['singleProductCreator'] = '创建单个产品';
$_LANG['addonAA']['productsCreator']['sslCenterProduct']     = 'SSLCenter 产品：';
$_LANG['addonAA']['productsCreator']['productName']          = '产品名称：';
$_LANG['addonAA']['productsCreator']['customguide']          = '自定义设置：';
$_LANG['addonAA']['productsCreator']['productGroup']         = '产品组：';
$_LANG['addonAA']['productsCreator']['autoSetup']            = '自动开通：';
$_LANG['addonAA']['productsCreator']['autoSetupOrder']       = '当客户下单之后（未付款）立即自动开通';
$_LANG['addonAA']['productsCreator']['autoSetupPayment']     = '当收到客户首付款时自动开通';
$_LANG['addonAA']['productsCreator']['autoSetupOn']          = '手动审核通过后自动开通';
$_LANG['addonAA']['productsCreator']['autoSetupOff']         = '手动开通';
$_LANG['addonAA']['productsCreator']['months']               = ' 月份：';



$_LANG['addonAA']['productsCreator']['enableSans']   = '启用子域名：';
$_LANG['addonAA']['productsCreator']['includedSans'] = '包括子域名：';

$_LANG['addonAA']['productsCreator']['pricing']             = '定价：';
$_LANG['addonAA']['productsCreator']['pricingMonthly']      = '月付/一次性';
$_LANG['addonAA']['productsCreator']['pricingQuarterly']    = '季付';
$_LANG['addonAA']['productsCreator']['pricingSemiAnnually'] = '半年';
$_LANG['addonAA']['productsCreator']['pricingAnnually']     = '年付';
$_LANG['addonAA']['productsCreator']['pricingBiennially']   = '两年';
$_LANG['addonAA']['productsCreator']['pricingTriennially']  = '三年';

$_LANG['addonAA']['productsCreator']['pricingSetupFee'] = '初装费';
$_LANG['addonAA']['productsCreator']['pricingPrice']    = '价格';
$_LANG['addonAA']['productsCreator']['pricingEnable']   = '启用';
$_LANG['addonAA']['productsCreator']['saveSingle']      = '创建产品';

$_LANG['addonAA']['productsCreator']['multipleProductCreator'] = '创建多个产品';
$_LANG['addonAA']['productsCreator']['saveMultiple']           = '创建产品';

$_LANG['addonAA']['productsCreator']['messages']['mass_product_created']    = '产品已被设置为隐藏产品。请转到“产品配置”以取消隐藏。在此之前，请检查产品配置并设置价格。';
$_LANG['addonAA']['productsCreator']['messages']['single_product_created']  = '产品已被设置为隐藏产品。请转到“产品配置”以取消隐藏。在此之前，请检查产品配置并设置价格。';
$_LANG['addonAA']['productsCreator']['messages']['no_product_group_found']  = '未找到产品组。';
$_LANG['addonAA']['productsCreator']['messages']['api_product_not_chosen']  = '未选择 SSLCenter 产品。';
$_LANG['addonAA']['productsCreator']['messages']['api_configuration_empty'] = 'API 配置为空，请检查 API 设置。';

$_LANG['addonAA']['productsConfiguration']['messages']['product_saved']          = '产品已保存。';
$_LANG['addonAA']['productsConfiguration']['messages']['configurable_generated'] = '产品的可配置选项已成功生成。';

$_LANG['addonAA']['productsConfiguration']['messages']['api_configuration_empty'] = 'API 配置为空，请检查 API 设置。';

$_LANG['addonAA']['pagesLabels']['label']['importSSLOrder']                      = '导入 SSL 订单';
$_LANG['addonAA']['importSSLOrder']['header']                                    = '导入 SSL 订单';
$_LANG['addonAA']['importSSLOrder']['order_id']['label']                         = 'API 订单 ID';
$_LANG['addonAA']['importSSLOrder']['client_id']['label']                        = '客户';
$_LANG['addonAA']['importSSLOrder']['importSSL']['content']                      = '导入';
$_LANG['addonAA']['importSSLOrder']['messages']['import_success']                = 'SSL 订单已成功导入。';
$_LANG['addonAA']['importSSLOrder']['messages']['order_id_not_provided']         = '尚未提供 API 订单 ID。';
$_LANG['addonAA']['importSSLOrder']['messages']['client_id_not_provided']        = '尚未提供客户 ID。';
$_LANG['addonAA']['importSSLOrder']['messages']['ssl_order_already_exist']       = '系统中已经存在此 ID 的 SLL 订单。';
$_LANG['addonAA']['importSSLOrder']['messages']['ssl_order_product_not_exist']   = '系统中不存在此 SSL 订单 ID 的产品。';
$_LANG['addonAA']['importSSLOrder']['messages']['order_create_error']            = '无法创建订单';
$_LANG['addonAA']['importSSLOrder']['messages']['no_payment_gateway_error']      = '尚未配置支付网关。';
$_LANG['addonAA']['importSSLOrder']['messages']['order_cancelled_import_unable'] = '无法导入已取消的SSL订单。';

$_LANG['addonAA']['userCommissions']['integrationCode']['header']         = '显示优惠码';
$_LANG['addonAA']['userCommissions']['pleaseNote']                        = '请注意：';
$_LANG['addonAA']['userCommissions']['info']                              = '若要在客户中心显示产品价格以及额外的优惠码，请执行以下操作：';
$_LANG['addonAA']['userCommissions']['info1']                             = '1. 打开以下文件';
$_LANG['addonAA']['userCommissions']['info2']                             = '2. 将以下代码添加至文件开头';
$_LANG['addonAA']['userCommissions']['info3']                             = '3. 打开以下文件';
$_LANG['addonAA']['userCommissions']['info4']                             = '4. 将以下代码添加至文件开头';
$_LANG['addonAA']['pagesLabels']['label']['userCommissions']              = '优惠码规则';
$_LANG['addonAA']['userCommissions']['title']                             = '优惠码规则';
$_LANG['addonAA']['userCommissions']['addNewCommissionRule']              = '添加新规则';
$_LANG['addonAA']['userCommissions']['editItem']                          = '编辑';
$_LANG['addonAA']['userCommissions']['deleteItem']                        = '删除';
$_LANG['addonAA']['userCommissions']['messages']['addSuccess']            = '优惠码规则添加成功。';
$_LANG['addonAA']['userCommissions']['messages']['removeSuccess']         = '优惠码规则删除成功。';
$_LANG['addonAA']['userCommissions']['messages']['updateSuccess']         = '优惠码规则更新成功。';
$_LANG['addonAA']['userCommissions']['messages']['clientIDNotProvided']   = '尚未提供客户 ID。';
$_LANG['addonAA']['userCommissions']['messages']['ruleIDNotProvided']     = '尚未提供规则 ID。';
$_LANG['addonAA']['userCommissions']['messages']['productIDNotProvided']  = '尚未提供产品 ID。';
$_LANG['addonAA']['userCommissions']['messages']['commissionNotProvided'] = '尚未提供优惠码。';

$_LANG['addonAA']['userCommissions']['table']['client']                       = '客户';
$_LANG['addonAA']['userCommissions']['table']['product']                      = '产品';
$_LANG['addonAA']['userCommissions']['table']['commission']                   = '折扣[%]';
$_LANG['addonAA']['userCommissions']['table']['monthly/onetime']              = '月付/一次性';
$_LANG['addonAA']['userCommissions']['table']['quarterly']                    = '季付';
$_LANG['addonAA']['userCommissions']['table']['semiannually']                 = '半年';
$_LANG['addonAA']['userCommissions']['table']['annually']                     = '年付';
$_LANG['addonAA']['userCommissions']['table']['biennially']                   = '两年';
$_LANG['addonAA']['userCommissions']['table']['triennially']                  = '三年';
$_LANG['addonAA']['userCommissions']['table']['actions']                      = '操作';
$_LANG['addonAA']['userCommissions']['modal']['pleaseSelecetOnePlaceholder']  = '请选择一个……';
$_LANG['addonAA']['userCommissions']['modal']['selectClientFirstPlaceholder'] = '请先选择一位客户……';
$_LANG['addonAA']['userCommissions']['modal']['pleaseSelectProductFirst']     = '请先选择一个产品……';
$_LANG['addonAA']['userCommissions']['modal']['noDataAvailable']              = '无可用数据。';
$_LANG['addonAA']['userCommissions']['modal']['noClientAvailable']            = '无可用客户。';
$_LANG['addonAA']['userCommissions']['modal']['noProductAvailable']           = '无可用产品。';
$_LANG['addonAA']['userCommissions']['table']['basePrice']                    = '基础价格：';
$_LANG['addonAA']['userCommissions']['table']['priceWithCommission']          = '优惠后的价格：';

$_LANG['addonAA']['userCommissions']['modal']['addCommissionRule']          = '添加新优惠码规则';
$_LANG['addonAA']['userCommissions']['modal']['client']                     = '客户';
$_LANG['addonAA']['userCommissions']['modal']['product']                    = '产品';
$_LANG['addonAA']['userCommissions']['modal']['commission']                 = '折扣[%]';
$_LANG['addonAA']['userCommissions']['modal']['add']                        = '添加';
$_LANG['addonAA']['userCommissions']['modal']['edit']                       = '保存修改';
$_LANG['addonAA']['userCommissions']['modal']['close']                      = '关闭';
$_LANG['addonAA']['userCommissions']['modal']['productPrice']               = '产品价格';
$_LANG['addonAA']['userCommissions']['modal']['productPriceWithCommission'] = '产品价格（折扣后）';
$_LANG['addonAA']['userCommissions']['modal']['monthly/onetime']            = '月付/一次性';
$_LANG['addonAA']['userCommissions']['modal']['quarterly']                  = '季付';
$_LANG['addonAA']['userCommissions']['modal']['semiannually']               = '半年';
$_LANG['addonAA']['userCommissions']['modal']['annually']                   = '年付';
$_LANG['addonAA']['userCommissions']['modal']['biennially']                 = '两年';
$_LANG['addonAA']['userCommissions']['modal']['triennially']                = '三年';
$_LANG['addonAA']['userCommissions']['modal']['removeRule']                 = '删除优惠码规则';
$_LANG['addonAA']['userCommissions']['modal']['remove']                     = '删除';
$_LANG['addonAA']['userCommissions']['modal']['removeRuleInfo']             = '您将要删除优惠码规则，【此过程不可逆】。';


$_LANG['anErrorOccurred'] = 'An error occurred';

$_LANG['pagesLabels']['label']['orders']       = '导入 SSL 订单';
$_LANG['addonCA']['sslSummary']['title']       = 'SSL 订单摘要';
$_LANG['addonCA']['sslSummary']['total']       = '总订单';
$_LANG['addonCA']['sslSummary']['unpaid']      = '未付款订单';
$_LANG['addonCA']['sslSummary']['processing']  = '正在处理的订单';
$_LANG['addonCA']['sslSummary']['expiresSoon'] = '即将过期的订单';

$_LANG['addonCA']['sslSummaryOrdersPage']['pageTitle']['total']        = '总订单';
$_LANG['addonCA']['sslSummaryOrdersPage']['pageTitle']['unpaid']       = '未付款订单';
$_LANG['addonCA']['sslSummaryOrdersPage']['pageTitle']['processing']   = '正在处理的订单';
$_LANG['addonCA']['sslSummaryOrdersPage']['pageTitle']['expires_soon'] = '即将过期的订单';
$_LANG['addonCA']['sslSummaryOrdersPage']['Product/Service']           = '产品/服务';
$_LANG['addonCA']['sslSummaryOrdersPage']['Pricing']                   = '定价';
$_LANG['addonCA']['sslSummaryOrdersPage']['Next Due Date']             = '到期时间';
$_LANG['addonCA']['sslSummaryOrdersPage']['Status']                    = '状态';

$_LANG['invalidEmailAddress']           = 'Email 地址不正确';
$_LANG['csrCodeGeneraterdSuccessfully'] = 'CSR 代码已成功生成';
$_LANG['invalidCountryCode']            = '国家代码不正确（请查阅 ISO 3166-1）';
$_LANG['csrCodeGeneraterFailed']        = 'CSR 代码生成失败';

$_LANG['viewAll']		= '浏览全部';
$_LANG['addonAA']['productsConfiguration']['save_all_products']       = '保存所有产品';
$_LANG['addonAA']['productsConfiguration']['products_saved']       = '产品已保存。';
