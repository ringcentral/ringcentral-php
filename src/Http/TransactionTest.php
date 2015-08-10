<?php

use RingCentral\SDK\Http\Transaction;
use RingCentral\SDK\Test\TestCase;

class TransactionTest extends TestCase
{

    public function testMultipart()
    {

        $goodMultipartMixedResponse =
            "Content-Type: multipart/mixed; boundary=Boundary_1245_945802293_1394135045248\n" .
            "\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\r\n" .
            "\r\n" .
            "{\"response\" : [{\"status\" : 200}, {\"status\" : 200}]\n" .
            "}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\"foo\": \"bar\"}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\"baz\" : \"qux\"}\n" .
            "--Boundary_1245_945802293_1394135045248--\n";

        $r = new Transaction(null, $goodMultipartMixedResponse, 207);
        $parts = $r->getMultipart();

        $this->assertEquals(2, count($parts));
        $this->assertEquals('bar', $parts[0]->getJson()->foo);
        $this->assertEquals('qux', $parts[1]->getJson()->baz);


    }

    public function testMultipartWithErrorPart()
    {

        $multipartMixedResponseWithErrorPart =
            "Content-Type: multipart/mixed; boundary=Boundary_1245_945802293_1394135045248\n" .
            "\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\"response\" : [{\"status\" : 200}, {\"status\" : 404}]\n" .
            "}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\"foo\" : \"bar\"}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\"message\" : \"object not found\"}\n" .
            "--Boundary_1245_945802293_1394135045248--\n";

        $r = new Transaction(null, $multipartMixedResponseWithErrorPart, 207);
        $parts = $r->getMultipart();

        $this->assertEquals(2, count($parts));
        $this->assertEquals('bar', $parts[0]->getJson()->foo);
        $this->assertEquals('object not found', $parts[1]->getError());

    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage JSON Error: Syntax error, malformed JSON
     */
    public function testMultipartCorruptedResponse()
    {

        $badMultipartMixedResponse =
            "Content-Type: multipart/mixed; boundary=Boundary_1245_945802293_1394135045248\n" .
            "\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "THIS IS JUNK AND CANNOT BE PARSED AS JSON\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\"foo\" : \"bar\"}\n" .
            "--Boundary_1245_945802293_1394135045248--\n";

        $r3 = new Transaction(null, $badMultipartMixedResponse, 207);
        $r3->getMultipart();

    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Response is not multipart
     */
    public function testMultipartOnNotAMultipartResponse()
    {

        $r3 = new Transaction(null, "Content-Type: text/plain\n\nWhatever", 207);
        $r3->getMultipart();

    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Boundary not found
     */
    public function testMultipartWitoutBoundary()
    {

        $response =
            "Content-Type: multipart/mixed\n" .
            "\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\r\n" .
            "\r\n" .
            "{\"response\" : [ {\"status\" : 200} ]}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\"foo\" : \"bar\"}\n" .
            "--Boundary_1245_945802293_1394135045248--\n";

        $r3 = new Transaction(null, $response, 207);
        $r3->getMultipart();

    }

    public function testGetJson()
    {

        $r = new Transaction(null, "content-type: application/json\n\n{\"foo\":\"bar\"}", 200);

        $this->assertEquals('{"foo":"bar"}', (string)$r->getResponse()->getBody());
        $this->assertEquals('bar', $r->getJson()->foo);

        $asArray = $r->getJson(false);
        $this->assertEquals('bar', $asArray['foo']);

    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Response is not JSON
     */
    public function testGetJsonWithNotJSON()
    {

        $r = new Transaction(null, "content-type: application/not-a-json\n\nfoo", 200);
        $r->getJson();

    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage JSON Error: Syntax error, malformed JSON
     */
    public function testGetJsonWithCorruptedJSON()
    {

        $r = new Transaction(null, "content-type: application/json\n\n{\"foo\";\"bar\"}", 200);
        $r->getJson();

    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage JSON Error: Result is empty after parsing
     */
    public function testGetJsonWithEmptyJSON()
    {

        $r = new Transaction(null, "content-type: application/json\n\nnull", 200);
        $r->getJson();

    }

}