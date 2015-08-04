<?php

use RingCentral\http\MultipartBuilder;
use RingCentral\test\TestCase;

class MultipartBuilderTest extends TestCase
{

    private $fname;

    public function setup()
    {
        $this->fname = tempnam('/tmp', 'tfile');

        if (file_exists($this->fname)) {
            unlink($this->fname);
        }
    }

    public function tearDown()
    {
        if (file_exists($this->fname)) {
            unlink($this->fname);
        }
    }


    public function testContentPlainText()
    {

        $expected =
            "--boundary\r\n" .
            "Content-Type: application/json\r\n" .
            "Content-Disposition: form-data; name=\"json\"; filename=\"request.json\"\r\n" .
            "Content-Length: 51\r\n" .
            "\r\n" .
            "{\"to\":{\"phoneNumber\":\"foo\"},\"faxResolution\":\"High\"}\r\n" .
            "--boundary\r\n" .
            "Content-Disposition: form-data; name=\"plain\"; filename=\"plain.txt\"\r\n" .
            "Content-Length: 10\r\n" .
            "Content-Type: text/plain\r\n" .
            "\r\n" .
            "plain text\r\n" .
            "--boundary--\r\n";

        $builder = new MultipartBuilder();

        $builder->setBody(array('to' => array('phoneNumber' => 'foo'), 'faxResolution' => 'High'))
                ->setBoundary('boundary')
                ->addAttachment('plain text', 'plain.txt', 'plain');

        $request = $builder->getRequest('/fax');

        $this->assertEquals($expected, (string)$request->getBody());

    }

    public function testContentStream()
    {

        $expected =
            "--boundary\r\n" .
            "Content-Type: application/json\r\n" .
            "Content-Disposition: form-data; name=\"json\"; filename=\"request.json\"\r\n" .
            "Content-Length: 51\r\n" .
            "\r\n" .
            "{\"to\":{\"phoneNumber\":\"foo\"},\"faxResolution\":\"High\"}\r\n" .
            "--boundary\r\n" .
            "Content-Disposition: form-data; name=\"streamed\"; filename=\"streamed.txt\"\r\n" .
            "Content-Length: 8\r\n" .
            "Content-Type: text/plain\r\n" .
            "\r\n" .
            "streamed\r\n" .
            "--boundary\r\n" .
            "Content-Disposition: form-data; name=\"streamed-no-file\"; filename=\"streamed-no-file\"\r\n" .
            "Content-Length: 8\r\n" .
            "\r\n" .
            "streamed\r\n" .
            "--boundary--\r\n";

        $builder = new MultipartBuilder();

        $builder->setBody(array('to' => array('phoneNumber' => 'foo'), 'faxResolution' => 'High'))
                ->setBoundary('boundary')
                ->addAttachment(\GuzzleHttp\Psr7\stream_for('streamed'), 'streamed.txt', 'streamed')
                ->addAttachment(\GuzzleHttp\Psr7\stream_for('streamed'), null, 'streamed-no-file');

        $this->assertEquals($expected, (string)$builder->getRequest('/fax')->getBody());

    }

    public function testContentResource()
    {

        file_put_contents($this->fname, 'file');

        $fileParts = explode('/', $this->fname);
        $fileName = array_pop($fileParts);

        $expected =
            "--boundary\r\n" .
            "Content-Type: application/json\r\n" .
            "Content-Disposition: form-data; name=\"json\"; filename=\"request.json\"\r\n" .
            "Content-Length: 51\r\n" .
            "\r\n" .
            "{\"to\":{\"phoneNumber\":\"foo\"},\"faxResolution\":\"High\"}\r\n" .
            "--boundary\r\n" .
            "Content-Disposition: form-data; name=\"file\"; filename=\"" . $fileName . "\"\r\n" .
            "Content-Length: 4\r\n" .
            "\r\n" .
            "file\r\n" .
            "--boundary\r\n" .
            "Content-Disposition: form-data; name=\"file\"; filename=\"file.txt\"\r\n" .
            "Content-Length: 4\r\n" .
            "Content-Type: text/plain\r\n" .
            "\r\n" .
            "file\r\n" .
            "--boundary--\r\n";

        $builder = new MultipartBuilder();

        $builder->setBody(array('to' => array('phoneNumber' => 'foo'), 'faxResolution' => 'High'))
                ->setBoundary('boundary')
                ->addAttachment(fopen($this->fname, 'r'), null, 'file')
                ->addAttachment(fopen($this->fname, 'r'), 'file.txt', 'file');

        $this->assertEquals($expected, (string)$builder->getRequest('/fax')->getBody());

    }

    public function testHeaders()
    {

        $expected =
            "--boundary\r\n" .
            "Content-Type: application/json\r\n" .
            "Content-Disposition: form-data; name=\"json\"; filename=\"request.json\"\r\n" .
            "Content-Length: 51\r\n" .
            "\r\n" .
            "{\"to\":{\"phoneNumber\":\"foo\"},\"faxResolution\":\"High\"}\r\n" .
            "--boundary\r\n" .
            "Content-Type: text/custom\r\n" . // <----- CUSTOM HEADER
            "Content-Disposition: form-data; name=\"plain\"; filename=\"plain.txt\"\r\n" .
            "Content-Length: 10\r\n" .
            "\r\n" .
            "plain text\r\n" .
            "--boundary--\r\n";

        $builder = new MultipartBuilder();

        $builder->setBody(array('to' => array('phoneNumber' => 'foo'), 'faxResolution' => 'High'))
                ->setBoundary('boundary')
                ->addAttachment('plain text', 'plain.txt', 'plain', array('Content-Type' => 'text/custom'));

        $request = $builder->getRequest('/fax');

        $this->assertEquals($expected, (string)$request->getBody());
        $this->assertEquals('multipart/form-data; boundary=boundary', $request->getHeaderLine('Content-Type'));
        $this->assertEquals('boundary', $builder->getBoundary());
        $this->assertEquals(1, count($builder->getAttachments()));

    }

    public function testBody()
    {

        $builder = new MultipartBuilder();
        $body = array('to' => array('phoneNumber' => 'foo'), 'faxResolution' => 'High');

        $builder->setBody($body)
                ->setBoundary('boundary');

        $this->assertEquals($body, $builder->getBody());

    }

}