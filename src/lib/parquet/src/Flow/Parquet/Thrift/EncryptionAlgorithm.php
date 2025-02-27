<?php

declare(strict_types=1);
namespace Flow\Parquet\Thrift;

/**
 * Autogenerated by Thrift Compiler (0.18.1).
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 *
 *  @generated
 */
use Thrift\Exception\{TProtocolException};
use Thrift\Type\{TType};

class EncryptionAlgorithm
{
    public static $_TSPEC = [
        1 => [
            'var' => 'AES_GCM_V1',
            'isRequired' => false,
            'type' => TType::STRUCT,
            'class' => '\Flow\Parquet\Thrift\AesGcmV1',
        ],
        2 => [
            'var' => 'AES_GCM_CTR_V1',
            'isRequired' => false,
            'type' => TType::STRUCT,
            'class' => '\Flow\Parquet\Thrift\AesGcmCtrV1',
        ],
    ];

    public static $isValidate = false;

    /**
     * @var AesGcmCtrV1
     */
    public $AES_GCM_CTR_V1;

    /**
     * @var AesGcmV1
     */
    public $AES_GCM_V1;

    public function __construct($vals = null)
    {
        if (is_array($vals)) {
            if (isset($vals['AES_GCM_V1'])) {
                $this->AES_GCM_V1 = $vals['AES_GCM_V1'];
            }

            if (isset($vals['AES_GCM_CTR_V1'])) {
                $this->AES_GCM_CTR_V1 = $vals['AES_GCM_CTR_V1'];
            }
        }
    }

    public function getName()
    {
        return 'EncryptionAlgorithm';
    }

    public function read($input)
    {
        $xfer = 0;
        $fname = null;
        $ftype = 0;
        $fid = 0;
        $xfer += $input->readStructBegin($fname);

        while (true) {
            $xfer += $input->readFieldBegin($fname, $ftype, $fid);

            if ($ftype == TType::STOP) {
                break;
            }

            switch ($fid) {
                case 1:
                    if ($ftype == TType::STRUCT) {
                        $this->AES_GCM_V1 = new AesGcmV1();
                        $xfer += $this->AES_GCM_V1->read($input);
                    } else {
                        $xfer += $input->skip($ftype);
                    }

                    break;
                case 2:
                    if ($ftype == TType::STRUCT) {
                        $this->AES_GCM_CTR_V1 = new AesGcmCtrV1();
                        $xfer += $this->AES_GCM_CTR_V1->read($input);
                    } else {
                        $xfer += $input->skip($ftype);
                    }

                    break;

                default:
                    $xfer += $input->skip($ftype);

                    break;
            }
            $xfer += $input->readFieldEnd();
        }
        $xfer += $input->readStructEnd();

        return $xfer;
    }

    public function write($output)
    {
        $xfer = 0;
        $xfer += $output->writeStructBegin('EncryptionAlgorithm');

        if ($this->AES_GCM_V1 !== null) {
            if (!is_object($this->AES_GCM_V1)) {
                throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
            }
            $xfer += $output->writeFieldBegin('AES_GCM_V1', TType::STRUCT, 1);
            $xfer += $this->AES_GCM_V1->write($output);
            $xfer += $output->writeFieldEnd();
        }

        if ($this->AES_GCM_CTR_V1 !== null) {
            if (!is_object($this->AES_GCM_CTR_V1)) {
                throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
            }
            $xfer += $output->writeFieldBegin('AES_GCM_CTR_V1', TType::STRUCT, 2);
            $xfer += $this->AES_GCM_CTR_V1->write($output);
            $xfer += $output->writeFieldEnd();
        }
        $xfer += $output->writeFieldStop();
        $xfer += $output->writeStructEnd();

        return $xfer;
    }
}
