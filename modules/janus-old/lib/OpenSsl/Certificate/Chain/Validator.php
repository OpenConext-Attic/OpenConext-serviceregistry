<?php

/**
 *
 */ 
class sspmod_janus_OpenSsl_Certificate_Chain_Validator
{
    const ERROR_PREFIX = 'OpenSSL: ';
    const WARNING_PREFIX = 'OpenSSL: ';
    
    protected $_chain;

    protected $_ignoreOnSelfSigned = false;

    protected $_warnOnSelfSigned = false;

    protected $_valid;

    protected $_warnings = array();

    protected $_errors = array();

    protected $_trustedRootCertificateAuthorityFile;

    public function __construct(sspmod_janus_OpenSsl_Certificate_Chain $chain)
    {
        $this->_chain = $chain;
    }

    public function setIgnoreSelfSigned($mustIgnore)
    {
        $this->_ignoreOnSelfSigned = $mustIgnore;
        return $this;
    }

    public function setWarnOnSelfSigned($mustWarn)
    {
        $this->_warnOnSelfSigned = $mustWarn;
        return $this;
    }

    public function setTrustedRootCertificateAuthorityFile($file)
    {
        $this->_trustedRootCertificateAuthorityFile = $file;
        return $this;
    }

    public function validate()
    {
        $this->_validateSequence();
        $this->_validateWithOpenSsl();
    }

    protected function _validateSequence()
    {
        $chainCertificates = $this->_chain->getCertificates();

        $certificate = array_shift($chainCertificates);
        $count = 1;

        $prevIssuer = $certificate->getIssuerDn();

        while(!empty($chainCertificates)) {
            $certificate = array_shift($chainCertificates);
            $count++;

            $subjectDn = $certificate->getSubjectDn();
            if ($prevIssuer !== $subjectDn) {
                $this->_valid = false;
                $this->_errors[] = "Problem in chain, certificate $count ($subjectDn) does not match the expected issuer ($prevIssuer)";
            }
            $prevIssuer = $certificate->getIssuerDn();
        }
    }

    protected function _validateWithOpenSsl()
    {
        $chainPems = '';
        $chainCertificates = $this->_chain->getCertificates();

        foreach ($chainCertificates as $certificate) {
            $chainPems = $certificate->getPem() . PHP_EOL . $chainPems;
        }

        $command = new sspmod_janus_OpenSSL_Command_Verify();
        if (isset($this->_trustedRootCertificateAuthorityFile)) {
            $command->setCertificateAuthorityFile($this->_trustedRootCertificateAuthorityFile);
        }

        // Open ssl command does not return it's error output when called indirectly
        $command->enableErrorToOutputRedirection();
        $command->execute($chainPems)->getOutput();

        $results = $command->getParsedResults();
        
        $this->_valid = $results['valid'];
        foreach ($results['errors'] as $openSslErrorCode => $openSslError) {
            if ($openSslErrorCode === OPENSSL_X509_V_ERR_DEPTH_ZERO_SELF_SIGNED_CERT) {
                if ($this->_ignoreOnSelfSigned) {
                    continue;
                }
                else if ($this->_warnOnSelfSigned) {
                    $this->_warnings[] = self::WARNING_PREFIX . $openSslError['description'];
                    continue;
                }
            }

            $this->_errors[] = self::ERROR_PREFIX . $openSslError['description'];
        }
    }

    public function isValid()
    {
        return $this->_valid;
    }

    public function getWarnings()
    {
        return $this->_warnings;
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}
