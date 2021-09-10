<?php namespace ItRail\AdTacker\Models;

use Model;
use Backend\Models\User;
use Backend\Models\UserGroup;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'ad_settings';
    public $settingsFields = 'fields.yaml';

    public function initSettingsData()
    {
        $this->registration_point = "";
        $this->referral_point = "";
        $this->daily_income_point = "";
        $this->fund_transfer_fee = "";
        $this->minimum_balance_to_transfer = "";
        $this->minimum_balance_to_withdraw = "";
        $this->fund_transfer_transaction_status = "";
        $this->withdraw_transaction_status = "";
    }

    public function getFundTransferTransactionStatusOptions()
    {
        $options = [
            null => 'Select type',
        ];
        $items = new TransactionType();

        $items->each(function ($item) use (&$options) {
            return $options[$item->id] = $item->name;
        });

        return $options;
    }

    public function getWithdrawTransactionStatusOptions()
    {
        $options = [
            null => 'Select type',
        ];
        $items = new TransactionType();

        $items->each(function ($item) use (&$options) {
            return $options[$item->id] = $item->name;
        });

        return $options;
    }
}
