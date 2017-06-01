<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {

        $currency = new Application_Model_DbTable_Currency();
        $history = new Application_Model_DbTable_History();

        $exchangeRates = $currency->fetchAll();
        $exchangeRates = $exchangeRates->toArray();

        if (count($exchangeRates) == 0) {
            // Get real and actual rates and store, if no stored data
            $exchangeRates = $currency->getExchangeRates();
            $currency->storeExchangeRates($exchangeRates);
        } else {
            $this->view->currencies = $exchangeRates;
        }

        $this->view->historyLogs = $history->getLastHistory();

    }

    public function calculateAmountAction()
    {
        $this->_helper->viewRenderer->setNoRender();

        $currencyIn = $this->getParam('currencyIn');
        $currencyOut = $this->getParam('currencyOut');
        $amountIn = $this->getParam('amountIn');

        if ($currencyIn && $currencyOut && $amountIn) {
            $currency = new Application_Model_DbTable_Currency();
            $amountOut = $currency->calculateAmount($currencyIn, $currencyOut, $amountIn);

            $this->_helper->json(['amountOut' => round($amountOut, 5)]);
        }

    }

    public function getLastHistoryAction($limit = 5)
    {

        $this->_helper->viewRenderer->setNoRender();

        $history = new Application_Model_DbTable_History();
        $historyLogs = $history->getLastHistory();

        $this->_helper->json($historyLogs);

    }

    public function cronUpdateAction()
    {
        $this->_helper->viewRenderer->setNoRender();

        $currency = new Application_Model_DbTable_Currency();
        $exchangeRates = $currency->getExchangeRates();

        foreach ($exchangeRates as $quote => $rate) {
            // Cut 'USD' from beginning of every quote
            $quote = substr($quote, 3);
            $currency->updateCurrency($quote, $rate);

        }

    }

}
