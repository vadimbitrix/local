<?php

namespace App;

class Agents
{
    /**
     * Получаем актуальный курс валют
     * @return string
     */
    public static function AgentGetCurrencyRate()
    {
        global $DB;

        // подключаем модуль "валют"
        if (!\CModule::IncludeModule('currency'))
            return '\\' . __METHOD__ . "();";

        $arCurList = array('USD', 'EUR');
        $bWarning = False;
        $rateDay = GetTime(time(), "SHORT", LANGUAGE_ID);
        $QUERY_STR = "date_req=" . $DB->FormatDate($rateDay, \CLang::GetDateFormat("SHORT", SITE_ID), "D.M.Y");
        $strQueryText = QueryGetData("www.cbr.ru", 80, "/scripts/XML_daily.asp", $QUERY_STR, $errno, $errstr);

        // данная строка нужна только для сайтов в кодировке utf8
        $strQueryText = iconv('windows-1251', 'utf-8', $strQueryText);

        if (strlen($strQueryText) <= 0)
            $bWarning = True;

        if (!$bWarning) {
            require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/xml.php");
            $objXML = new \CDataXML();
            $objXML->LoadString($strQueryText);
            $arData = $objXML->GetArray();
            $arFields = array();
            $arCurRate["CURRENCY_CBRF"] = array();

            if (is_array($arData) && count($arData["ValCurs"]["#"]["Valute"]) > 0) {
                for ($j1 = 0; $j1 < count($arData["ValCurs"]["#"]["Valute"]); $j1++) {
                    $arFields = array(
                        "CURRENCY" => $arData["ValCurs"]["#"]["Valute"][$j1]["#"]["CharCode"][0]["#"],
                        'DATE_RATE' => $rateDay,
                        'RATE' => DoubleVal(str_replace(",", ".", $arData["ValCurs"]["#"]["Valute"][$j1]["#"]["Value"][0]["#"])),
                        'RATE_CNT' => IntVal($arData["ValCurs"]["#"]["Valute"][$j1]["#"]["Nominal"][0]["#"]),
                    );
                    \CCurrencyRates::Add($arFields);
                }

            }
        }

        return '\\' . __METHOD__ . "();";
    }
}
