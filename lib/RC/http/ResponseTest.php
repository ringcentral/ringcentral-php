<?php

use RC\http\Response;
use RC\test\TestCase;

class ResponseTest extends TestCase
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

        $r1 = new Response(207, $goodMultipartMixedResponse);
        $this->assertEquals(2, count($r1->getResponses()));
        $this->assertEquals('bar', $r1->getResponses()[0]->getJson()->foo);
        $this->assertEquals('qux', $r1->getResponses()[1]->getJson()->baz);

        $r2 = new Response(207, $multipartMixedResponseWithErrorPart);
        $this->assertEquals('bar', $r2->getResponses()[0]->getJson()->foo);
        $this->assertEquals('object not found', $r2->getResponses()[1]->getError());
        $this->assertEquals('qux', $r2->getResponses()[2]->getJson()->baz);

        $r3 = new Response(207, $badMultipartMixedResponse);
        $caught = false;
        try {
            $r3->getResponses();
        } catch (Exception $e) {
            $caught = true;
        }
        $this->assertTrue($caught);

    }

}