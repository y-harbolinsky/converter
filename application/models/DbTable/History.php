<?php

class Application_Model_DbTable_History extends Zend_Db_Table_Abstract
{

    protected $_name = 'exchange_history';

    public function getLastHistory($limit = 5)
    {
        $select = $this->select()
            ->order('created DESC')
            ->limit($limit);

        $historyLogs = $this->fetchAll($select);

        return $historyLogs->toArray();
    }

    public function addHistory($currency_in, $amount, $result, $currency_out)
    {
        $this->insert([
            'currency_in' => $currency_in,
            'amount' => $amount,
            'result' => $result,
            'currency_out' => $currency_out,
            'created' => date('Y-m-d H:i:s'),
        ]);
    }

}
