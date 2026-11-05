<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

class AdminCustomButtonArray {

    public function run(array $params = []) {
        // S/MIME certificates (API v2): the SSL buttons do not apply
        if (!empty($params) && \MGModule\SSLCENTERWHMCS\eHelpers\SmimeProduct::isSmimeByServiceParams($params)) {
            return [
                'Refresh S/MIME Details' => 'SSLAdminRefreshSmime',
            ];
        }

        return [
            'Manage SSL'            => 'SSLAdminManageSSL',
            'Resend Approver Email' => 'SSLAdminResendApproverEmail',
            'Resend Certificate'    => 'SSLAdminResendCertificate',
            'Change Approver Email' => 'SSLAdminChangeApproverEmail',
            'Reissue Certificate'   => 'SSLAdminReissueCertificate',
            'View Certificate'      => 'SSLAdminViewCertificate',
            'Recheck Certificate Details' => 'SSLAdminRecheckCertificateDetails'
        ];
    }
}
