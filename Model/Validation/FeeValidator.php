<?php

namespace Billink\Billink\Model\Validation;

use Billink\Billink\Helper\Fee;
use Billink\Billink\Model\Config\Source\UsedWorkflow;
use Magento\Framework\Validator\AbstractValidator;

class FeeValidator extends AbstractValidator
{
    public function isValid($value)
    {
        $netherlandsB2CConfigured = false;
        $netherlandsB2CAlwaysZero = true;
        $otherB2CAlwaysZero = true;

        foreach ($value->getValue() as $row) {
            if (!is_array($row)) { continue; }

            if ($row[Fee::INDEX_WORKFLOW_TYPE] === UsedWorkflow::CONFIG_WORKFLOW_PRIVATE) {
                if ($row[Fee::COUNTRY] === "NL") {
                    $netherlandsB2CConfigured = true;
                    if (floatval($row[Fee::INDEX_AMOUNT]) > 0) {
                        $netherlandsB2CAlwaysZero = false;
                    }
                } elseif ($row[Fee::COUNTRY] === "other") {
                    if (floatval($row[Fee::INDEX_AMOUNT]) > 0) {
                        $otherB2CAlwaysZero = false;
                    }
                }
            }
        }

        if ($netherlandsB2CConfigured) {
            if ($netherlandsB2CAlwaysZero) {
                return true;
            }

            $this->_addMessages([__("Dutch B2C fee should always be zero")]);
            return false;
        }

        if (!$otherB2CAlwaysZero) {
            $this->_addMessages([__("Dutch B2C fee should always be zero, but 'Other' setting sets it")]);
            return false;
        }

        return true;
    }
}
