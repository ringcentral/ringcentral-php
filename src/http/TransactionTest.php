<?php

use RingCentral\http\Transaction;
use RingCentral\test\TestCase;

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
            "{\n" .
            "  \"response\" : [ {\n" .
            "    \"status\" : 200\n" .
            "  }, {\n" .
            "    \"status\" : 200\n" .
            "  } ]\n" .
            "}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\n" .
            "  \"foo\" : \"bar\"\n" .
            "}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\n" .
            "  \"baz\" : \"qux\"\n" .
            "}\n" .
            "--Boundary_1245_945802293_1394135045248--\n";

        $multipartMixedResponseWithErrorPart =
            "Content-Type: multipart/mixed; boundary=Boundary_1245_945802293_1394135045248\n" .
            "\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\n" .
            "  \"response\" : [ {\n" .
            "    \"status\" : 200\n" .
            "  }, {\n" .
            "    \"status\" : 404\n" .
            "  }, {\n" .
            "    \"status\" : 200\n" .
            "  } ]\n" .
            "}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\n" .
            "  \"foo\" : \"bar\"\n" .
            "}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\n" .
            "  \"message\" : \"object not found\"\n" .
            "}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\n" .
            "  \"baz\" : \"qux\"\n" .
            "}\n" .
            "--Boundary_1245_945802293_1394135045248--\n";

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
            "{\n" .
            "  \"foo\" : \"bar\"\n" .
            "}\n" .
            "--Boundary_1245_945802293_1394135045248\n" .
            "Content-Type: application/json\n" .
            "\n" .
            "{\n" .
            "  \"baz\" : \"qux\"\n" .
            "}\n" .
            "--Boundary_1245_945802293_1394135045248--\n";

        $r1 = new Transaction(null, $goodMultipartMixedResponse, 207);
        $this->assertEquals(2, count($r1->getMultipart()));
        $rr1 = $r1->getMultipart();
        $this->assertEquals('bar', $rr1[0]->getJson()->foo);
        $this->assertEquals('qux', $rr1[1]->getJson()->baz);

        $r2 = new Transaction(null, $multipartMixedResponseWithErrorPart, 207);
        $rr2 = $r2->getMultipart();
        $this->assertEquals('bar', $rr2[0]->getJson()->foo);
        $this->assertEquals('object not found', $rr2[1]->getError());
        $this->assertEquals('qux', $rr2[2]->getJson()->baz);

        $r3 = new Transaction(null, $badMultipartMixedResponse, 207);
        $caught = false;
        try {
            $r3->getMultipart();
        } catch (Exception $e) {
            $caught = true;
        }
        $this->assertTrue($caught);

    }

    public function testGetJson()
    {

        $validJson = 'content-type: application/json' . PHP_EOL .
                     '' . PHP_EOL .
                     '{"foo":"bar"}';


        $r = new Transaction(null, $validJson, 200);

        $this->assertEquals('{"foo":"bar"}', $r->getResponse()->getBody()->__toString());
        $this->assertEquals('bar', $r->getJson()->foo);

    }

}