<?php

namespace MGModule\GGSSLWHMCS\eServices\provisioning;

class AdminCustomButtonArray {

    public function run() {
        return [
            'Resend Approver Email' => 'SSLAdminResendApproverEmail',
            'Resend Certificate'    => 'SSLAdminResendCertificate',
            'Change Approver Email' => 'SSLAdminChangeApproverEmail',
            'Reissue Certificate'   => 'SSLAdminReissueCertificate',
            'View Certificate'      => 'SSLAdminViewCertificate',
        ];
    }
}
