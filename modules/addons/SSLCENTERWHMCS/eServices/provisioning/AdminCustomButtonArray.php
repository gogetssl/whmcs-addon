<?php

namespace MGModule\SSLCENTERWHMCS\eServices\provisioning;

class AdminCustomButtonArray {

    public function run() {
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
