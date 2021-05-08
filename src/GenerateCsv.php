#!/usr/bin/php
<?php
/**
 * WhittleCo 2021
 */

namespace WhittleCo;

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * Class GenerateCsv
 *
 * @author Jose Reboredo <jose@beautystack.com>
 */
class GenerateCsv
{
    const FILE_NAME = 'comments.csv';

    /**
     * @var Client
     */
    private $client;

    /**
     * GenerateCsv constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param string $url
     * @param $accessToken
     * @throws GuzzleException
     */
    public function __invoke(string $url, $accessToken)
    {
        $data = [];
        $response = $this->doRequest($url . '?access_token=' . $accessToken);

        $i = 0;
        print_r('First request: ' . ++$i . PHP_EOL);

        do {
            $body = json_decode((string)$response->getBody(), true);
            if (!empty($body['data'])) {
                array_push($data, ...$body['data']);
            }
            if (!empty($body['paging']['next'])) {
                $response = $this->doRequest($body['paging']['next']);
                print_r('Request done: ' . PHP_EOL);
            }
            print_r('NEXT request: ' . ++$i . PHP_EOL);
        } while (!empty($body['paging']['next']));

        print_r('Out: ' . PHP_EOL);

        $this->generateCsvFile($data);

        print_r('File generated: ' . PHP_EOL);
    }

    /**
     * @param string $url
     * @return ResponseInterface
     * @throws GuzzleException
     */
    protected function doRequest(string $url): ResponseInterface
    {
        return $this->client->get($url);
    }

    /**
     * @param array $data
     */
    protected function generateCsvFile(array $data)
    {
        $fp = fopen(self::FILE_NAME, 'w');
        foreach ($data as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);
    }
}

$url = 'https://graph.facebook.com/v10.0/17888807132123612/comments';
$accessToken = '';

(new GenerateCsv())
    ->__invoke($url, $accessToken);



