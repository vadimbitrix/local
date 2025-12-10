<?php

namespace Vadim24\Lib;

class Bitrix24Client
{
    private string $accessToken;
    private string $domain;
    private CURL $curl;

    public function __construct(string $accessToken, string $domain, CURL $curl)
    {
        $this->accessToken = $accessToken;
        $this->domain = $domain;
        $this->curl = $curl;
    }

    public function createDeal(array $data): array
    {
        $url = "https://{$this->domain}/rest/1/{$this->accessToken}/crm.deal.add.json";
        $response = $this->curl->makeRequest($url, 'POST', http_build_query($data));

        return json_decode($response, true);
    }
    public function createLead(array $data): array
    {
        $url = "https://{$this->domain}/rest/1/{$this->accessToken}/crm.lead.add.json";
        $response = $this->curl->makeRequest($url, 'POST', http_build_query($data));

        return json_decode($response, true);
    }
    public function getDealFields(): array
    {
        $url = "https://{$this->domain}/rest/1/{$this->accessToken}/crm.deal.fields.json";
        $response = $this->curl->makeRequest($url, 'GET');

        return json_decode($response, true);
    }
    public function getCompanyFields(): array
    {
        $url = "https://{$this->domain}/rest/1/{$this->accessToken}/crm.company.fields.json";
        $response = $this->curl->makeRequest($url, 'GET');

        return json_decode($response, true);
    }
    public function getRequisiteFields(): array
    {
        $url = "https://{$this->domain}/rest/1/{$this->accessToken}/crm.requisite.bankdetail.fields.json";
        $response = $this->curl->makeRequest($url, 'GET');

        return json_decode($response, true);
    }
    public function getRequisiteList(): array
    {
        $url = "https://{$this->domain}/rest/1/{$this->accessToken}/crm.requisite.list.json";
        $response = $this->curl->makeRequest($url, 'GET');

        return json_decode($response, true);
    }
    public function getContactFields(): array
    {
        $url = "https://{$this->domain}/rest/1/{$this->accessToken}/crm.contact.fields.json";
        $response = $this->curl->makeRequest($url, 'GET');

        return json_decode($response, true);
    }
     public function searchContactByPhone(string $phone): array
    {
        $params = [
            'filter' => ['PHONE' => $phone]
        ];

        $url = "https://{$this->domain}/rest/1/{$this->accessToken}/crm.contact.list.json";
        $response = $this->curl->makeRequest($url, 'GET', http_build_query($params));
        return json_decode($response, true);
    }

    public function createContact(array $data): array
    {

        $url = "https://{$this->domain}/rest/1/{$this->accessToken}/crm.contact.add.json";
        $response = $this->curl->makeRequest($url, 'POST', http_build_query($data));

        return json_decode($response, true);
    }
    public function createCompany(array $data): array {
        $url = "https://{$this->domain}/rest/1/{$this->accessToken}/crm.company.add.json";
        $response = $this->curl->makeRequest($url, 'POST', http_build_query($data));

        return json_decode($response, true);
    }
    public function searchCompanyByINN(string $inn): array {
        $params = ['filter' => ['UF_CRM_1702225807195' => $inn]];
        $url = "https://{$this->domain}/rest/1/{$this->accessToken}/crm.company.list.json";
        $response = $this->curl->makeRequest($url, 'GET', http_build_query($params));

        return json_decode($response, true);
    }
    // Создание реквизита
    public function createRequisite(array $data): array {
        $url = "https://{$this->domain}/rest/1/{$this->accessToken}/crm.requisite.add.json";
        $response = $this->curl->makeRequest($url, 'POST', http_build_query($data));

        return json_decode($response, true);
    }
    // Создание банковских данных к реквизиту
    public function createBankDetailRequisite(array $data): array {
        $url = "https://{$this->domain}/rest/1/{$this->accessToken}/crm.requisite.bankdetail.add.json";
        $response = $this->curl->makeRequest($url, 'POST', http_build_query($data));

        return json_decode($response, true);
    }
}
