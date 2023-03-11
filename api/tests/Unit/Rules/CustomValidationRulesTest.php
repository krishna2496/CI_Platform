<?php

namespace Tests\Unit\Rules;

use TestCase;
use Validator;

/**
 * @coversDefaultClass  \App\Rules\CustomValidationRules
 */
class UserServiceTest extends TestCase
{
    /**
     * Data provider for ::testLinkedInUrl().
     *
     * @return  array<string, array<int, mixed>>
     */
    public function linkedInUrlData(): array
    {
        return [
            'Fail: from pentest' => [
                'https://secarma.com/#linkedin.comcom',
                false,
            ],
            'Fail: non-linkedin, with linkedin hash' => [
                'https://acme.com/#linkedin.com',
                false,
            ],
            'Fail: non-linkedin, with linkedin query string' => [
                'https://acme.com/?linkedin.com',
                false,
            ],
            'Fail: domain only, no subdomain' => [
                'linkedin.com',
                false,
            ],
            'Fail: domain only, with www subdomain' => [
                'www.linkedin.com',
                false,
            ],
            'Fail: domain only, with country subdomain' => [
                'be.linkedin.com',
                false,
            ],
            'Fail: protocol and domain only no subdomain' => [
                'https://linkedin.com',
                false,
            ],
            'Fail: protocol and domain only, with www subdomain' => [
                'https://www.linkedin.com',
                false,
            ],
            'Fail: protocol and domain only, with country subdomain' => [
                'https://be.linkedin.com',
                false,
            ],
            'Fail: company path only, with trailing slash' => [
                'https://www.linkedin.com/company/',
                false,
            ],
            'Fail: company path only, without trailing slash' => [
                'https://www.linkedin.com/company',
                false,
            ],
            'Fail: personal path only, with trailing slash' => [
                'https://www.linkedin.com/in/',
                false,
            ],
            'Fail: personal path only, without trailing slash' => [
                'https://www.linkedin.com/in',
                false,
            ],
            'Fail: non-profile, with trailing slash' => [
                'https://www.linkedin.com/some/path/',
                false,
            ],
            'Fail: non-profile, without trailing slash' => [
                'https://www.linkedin.com/some/path',
                false,
            ],

            // Company profile

            'Pass: company profile, with trailing slash' => [
                'https://www.linkedin.com/company/acme/',
                true,
            ],
            'Pass: company profile, without trailing slash' => [
                'https://www.linkedin.com/company/acme',
                true,
            ],
            'Fail: company profile, with non-https, with trailing slash' => [
                'http://www.linkedin.com/company/acme/',
                false,
            ],
            'Pass: company profile, without www subdomain, with trailing slash' => [
                'https://linkedin.com/company/acme/',
                true,
            ],
            'Pass: company profile, with country subdomain, with trailing slash' => [
                'https://be.linkedin.com/company/acme/',
                true,
            ],
            'Fail: company profile, with trailing slash, with newline' => [
                "https://www.linkedin.com/company/acme/\n",
                false,
            ],
            'Fail: company profile, without trailing slash, with newline' => [
                "https://www.linkedin.com/company/acme\n",
                false,
            ],
            'Pass: company profile, without trailing slash, with query string' => [
                'https://www.linkedin.com/company/acme?test=true',
                true,
            ],
            'Fail: company profile, without trailing slash, with query string, with newline' => [
                "https://www.linkedin.com/company/acme?test=true\n",
                false,
            ],
            'Pass: company profile, without trailing slash, with hash' => [
                'https://www.linkedin.com/company/acme#test',
                true,
            ],
            'Fail: company profile, without trailing slash, with hash, with newline' => [
                "https://www.linkedin.com/company/acme#test\n",
                false,
            ],
            'Pass: company profile, with trailing slash, with query string' => [
                'https://www.linkedin.com/company/acme/?test=test',
                true,
            ],
            'Fail: company profile, with trailing slash, with query string, with newline' => [
                "https://www.linkedin.com/company/acme/?test=test\n",
                false,
            ],
            'Pass: company profile, with trailing slash, with hash' => [
                'https://www.linkedin.com/company/acme/#test',
                true,
            ],
            'Fail: company profile, with trailing slash, with hash, with newline' => [
                "https://www.linkedin.com/company/acme/#test\n",
                false,
            ],
            'Pass: company profile, with dash, without trailing slash' => [
                'https://www.linkedin.com/company/acme-inc',
                true,
            ],
            'Pass: company profile, with dash, with trailing slash' => [
                'https://www.linkedin.com/company/acme-inc/',
                true,
            ],
            'Fail: company profile, with underscore in company name, without trailing slash' => [
                'https://www.linkedin.com/company/acme_inc',
                false,
            ],
            'Fail: company profile, with underscore in company name, with trailing slash' => [
                'https://www.linkedin.com/company/acme_inc/',
                false,
            ],
            'Fail: company profile, with period in company name, without trailing slash' => [
                'https://www.linkedin.com/company/acme.inc',
                false,
            ],
            'Fail: company profile, with period in company name, with trailing slash' => [
                'https://www.linkedin.com/company/acme.inc',
                false,
            ],

            // Personal profile

            'Pass: user profile, with trailing slash' => [
                'https://www.linkedin.com/in/john/',
                true,
            ],
            'Pass: user profile, without trailing slash' => [
                'https://www.linkedin.com/in/john',
                true,
            ],
            'Fail: user profile, with non-https, with trailing slash' => [
                'http://www.linkedin.com/in/john/',
                false,
            ],
            'Pass: user profile, without www subdomain, with trailing slash' => [
                'https://linkedin.com/in/john/',
                true,
            ],
            'Pass: user profile, with country subdomain, with trailing slash' => [
                'https://be.linkedin.com/in/john/',
                true,
            ],
            'Fail: user profile, with trailing slash, with newline' => [
                "https://www.linkedin.com/in/john/\n",
                false,
            ],
            'Fail: user profile, without trailing slash, with newline' => [
                "https://www.linkedin.com/in/john\n",
                false,
            ],
            'Pass: user profile, without trailing slash, with query string' => [
                'https://www.linkedin.com/in/john?test=true',
                true,
            ],
            'Fail: user profile, without trailing slash, with query string, with newline' => [
                "https://www.linkedin.com/in/john?test=true\n",
                false,
            ],
            'Pass: user profile, without trailing slash, with hash' => [
                'https://www.linkedin.com/in/john#test',
                true,
            ],
            'Fail: user profile, without trailing slash, with hash, with newline' => [
                "https://www.linkedin.com/in/john#test\n",
                false,
            ],
            'Pass: user profile, with trailing slash, with query string' => [
                'https://www.linkedin.com/in/john/?test=test',
                true,
            ],
            'Fail: user profile, with trailing slash, with query string, with newline' => [
                "https://www.linkedin.com/in/john/?test=test\n",
                false,
            ],
            'Pass: user profile, with trailing slash, with hash' => [
                'https://www.linkedin.com/in/john/#test',
                true,
            ],
            'Fail: user profile, with trailing slash, with hash, with newline' => [
                "https://www.linkedin.com/in/john/#test\n",
                false,
            ],
            'Pass: user profile, with dash, without trailing slash' => [
                'https://www.linkedin.com/in/john-smith',
                true,
            ],
            'Pass: user profile, with dash, with trailing slash' => [
                'https://www.linkedin.com/in/john-smith/',
                true,
            ],
            'Fail: user profile, with underscore in company name, without trailing slash' => [
                'https://www.linkedin.com/in/john_smith',
                false,
            ],
            'Fail: user profile, with underscore in company name, with trailing slash' => [
                'https://www.linkedin.com/in/john_smith/',
                false,
            ],
            'Fail: user profile, with period in company name, without trailing slash' => [
                'https://www.linkedin.com/in/john.smith',
                false,
            ],
            'Fail: user profile, with period in company name, with trailing slash' => [
                'https://www.linkedin.com/in/john.smith',
                false,
            ],
        ];
    }

    /**
     * @param  string  $url
     * @param  bool    $pass
     *
     * @covers  ::validate
     *
     * @dataProvider  linkedInUrlData
     */
    public function testLinkedInUrl($url, $pass): void
    {
        $field = 'linkedin_url';

        $request = [
            $field => $url,
        ];

        $fields = [
            $field => 'url|valid_linkedin_url',
        ];

        $validator = Validator::make($request, $fields);

        $this->assertSame($pass, $validator->passes());

        // Make sure any error is from the field
        if (!$pass) {
            $messages = $validator->errors()->messages();

            $this->assertCount(1, $messages);
            $this->assertArrayHasKey($field, $messages);
        }
    }
}
