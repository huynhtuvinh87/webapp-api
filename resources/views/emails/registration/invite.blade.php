@extends('emails.layout')

@section('logo', 'logo_white')

@section('background', '#0B6739')

@section('content')
    <table class="row" style="border-collapse: collapse; border-spacing: 0; display: table; padding: 0; position: relative; text-align: left; vertical-align: top; width: 100%;">
        <tbody>
        <tr style="padding: 0; text-align: left; vertical-align: top;">
            <th class="small-12 large-12 columns first last" style="Margin: 0 auto; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 16px; padding-right: 16px; text-align: left; width: 564px;">
                <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                    <tbody>
                    <tr style="padding: 0; text-align: left; vertical-align: top;">
                        <th style="Margin: 0; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0; padding: 0; text-align: left;">
                            <table class="spacer" style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                <tbody>
                                <tr style="padding: 0; text-align: left; vertical-align: top;">
                                    <td height="32px" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 32px; font-weight: normal; hyphens: auto; line-height: 32px; margin: 0; mso-line-height-rule: exactly; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">&nbsp;</td>
                                </tr>
                                </tbody>
                            </table>
                            <h4 style="Margin: 0; Margin-bottom: 10px; color: inherit; font-family: Helvetica, Arial, sans-serif; font-size: 24px; font-weight: normal; line-height: 1.3; margin: 0; margin-bottom: 10px; padding: 0; text-align: left; word-wrap: normal;">Mandatory Requirement for All Contractors of {{ $orgName }}</h4>
                            <p style="Margin: 0; Margin-bottom: 10px; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0; margin-bottom: 10px; padding: 0; text-align: left;">You are required to complete this mandatory two minute registration as an active Contractor of {{ $orgName }}</p>
                            <p style="Margin: 0; Margin-bottom: 10px; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0; margin-bottom: 10px; padding: 0; text-align: left;">You must register and complete your Contractor requirements to remain eligible to go on-site.</p>
                            <table class="spacer" style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                <tbody>
                                <tr style="padding: 0; text-align: left; vertical-align: top;">
                                    <td height="32px" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 32px; font-weight: normal; hyphens: auto; line-height: 32px; margin: 0; mso-line-height-rule: exactly; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">&nbsp;</td>
                                </tr>
                                </tbody>
                            </table>
                            <table class="button large expand" style="Margin: 0 0 16px 0; border-collapse: collapse; border-spacing: 0; margin: 0 0 16px 0; padding: 0; text-align: left; vertical-align: top; width: 100% !important;">
                                <tbody>
                                <tr style="padding: 0; text-align: left; vertical-align: top;">
                                    <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
                                        <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                            <tbody>
                                            <tr style="padding: 0; text-align: left; vertical-align: top;">
                                                <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; background: #F69131; border: 2px solid #F69131; border-collapse: collapse !important; color: #ffffff; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
                                                    <center data-parsed="" style="min-width: 0; width: 100%;"><a href="{{ $registration_url }}" align="center" class="float-center" style="Margin: 0; border: 0 solid #2199e8; border-radius: 3px; color: #ffffff; display: inline-block; font-family: Helvetica, Arial, sans-serif; font-size: 20px; font-weight: bold; line-height: 1.3; margin: 0; padding: 10px 20px 10px 20px; padding-left: 0; padding-right: 0; text-align: center; text-decoration: none; width: 100%;">Register Now</a></center>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td class="expander" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 0 !important; text-align: left; vertical-align: top; visibility: hidden; width: 0; word-wrap: break-word;"></td>
                                </tr>
                                </tbody>
                            </table>
                            <table class="spacer" style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                <tbody>
                                <tr style="padding: 0; text-align: left; vertical-align: top;">
                                    <td height="32px" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 32px; font-weight: normal; hyphens: auto; line-height: 32px; margin: 0; mso-line-height-rule: exactly; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">&nbsp;</td>
                                </tr>
                                </tbody>
                            </table>
                            <table class="row" style="border-collapse: collapse; border-spacing: 0; display: table; padding: 0; position: relative; text-align: left; vertical-align: top; width: 100%;">
                                <tbody>
                                <tr style="padding: 0; text-align: left; vertical-align: top;">
                                    <th class="small-12 large-4 columns first" style="Margin: 0 auto; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 0 !important; padding-right: 0 !important; text-align: left; width: 33.33333%;">
                                        <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                            <tbody>
                                            <tr style="padding: 0; text-align: left; vertical-align: top;">
                                                <th style="Margin: 0; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0; padding: 0; text-align: left;">
                                                    <center data-parsed="" style="min-width: none !important; width: 100%;"> <img src="{{ config('api.url') }}/images/misc/person-placeholder.png" style="-ms-interpolation-mode: bicubic; Margin: 0 auto; clear: both; display: block; float: none; margin: 0 auto; max-width: 150px; outline: none; text-align: center; text-decoration: none; width: auto;"
                                                                                                                                  align="center" class="float-center"> </center>
                                                    <h5 class="text-center" style="Margin: 0; Margin-bottom: 10px; color: inherit; font-family: Helvetica, Arial, sans-serif; font-size: 20px; font-weight: normal; line-height: 1.3; margin: 0; margin-bottom: 10px; padding: 0; text-align: center; word-wrap: normal;"><strong>Step 1: Register</strong></h5>
                                                    <p class="text-center" style="Margin: 0; Margin-bottom: 10px; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0; margin-bottom: 10px; padding: 0; text-align: center;">Click on 'Sign Up' and follow the steps. There is an annual fee of $190USD plus applicable taxes per year. If you are already registered, log in, search for the company name and click 'Add Customer'.</p>
                                                </th>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </th>
                                    <th class="small-12 large-4 columns" style="Margin: 0 auto; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 0 !important; padding-right: 0 !important; text-align: left; width: 33.33333%;">
                                        <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                            <tbody>
                                            <tr style="padding: 0; text-align: left; vertical-align: top;">
                                                <th style="Margin: 0; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0; padding: 0; text-align: left;">
                                                    <center data-parsed="" style="min-width: none !important; width: 100%;"> <img src="{{ config('api.url') }}/images/misc/file-upload.png" style="-ms-interpolation-mode: bicubic; Margin: 0 auto; clear: both; display: block; float: none; margin: 0 auto; max-width: 150px; outline: none; text-align: center; text-decoration: none; width: auto;"
                                                                                                                                  align="center" class="float-center"> </center>
                                                    <h5 class="text-center" style="Margin: 0; Margin-bottom: 10px; color: inherit; font-family: Helvetica, Arial, sans-serif; font-size: 20px; font-weight: normal; line-height: 1.3; margin: 0; margin-bottom: 10px; padding: 0; text-align: center; word-wrap: normal;"><strong>Step 2: Upload</strong></h5>
                                                    <p class="text-center" style="Margin: 0; Margin-bottom: 10px; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0; margin-bottom: 10px; padding: 0; text-align: center;">Simply satisfy the requirements assigned to you based on the work you perform for your client by uploading, reviewing official documentation, taking in-app tests, or providing certifications and/or licenses.</p>
                                                </th>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </th>
                                    <th class="small-12 large-4 columns last" style="Margin: 0 auto; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 0 !important; padding-right: 0 !important; text-align: left; width: 33.33333%;">
                                        <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                            <tbody>
                                            <tr style="padding: 0; text-align: left; vertical-align: top;">
                                                <th style="Margin: 0; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0; padding: 0; text-align: left;">
                                                    <center data-parsed="" style="min-width: none !important; width: 100%;"> <img src="{{ config('api.url') }}/images/misc/file-check.png" style="-ms-interpolation-mode: bicubic; Margin: 0 auto; clear: both; display: block; float: none; margin: 0 auto; max-width: 120px; outline: none; text-align: center; text-decoration: none; width: auto;"
                                                                                                                                  align="center" class="float-center"> </center>
                                                    <h5 class="text-center" style="Margin: 0; Margin-bottom: 10px; color: inherit; font-family: Helvetica, Arial, sans-serif; font-size: 20px; font-weight: normal; line-height: 1.3; margin: 0; margin-bottom: 10px; padding: 0; text-align: center; word-wrap: normal;"><strong>Step 3: Compliance</strong></h5>
                                                    <p class="text-center" style="Margin: 0; Margin-bottom: 10px; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0; margin-bottom: 10px; padding: 0; text-align: center;">Store and manage documents that prove you are legally compliant to provide services to your clients. Network with organizations that are searching for the skills you possess by adding your type of work.</p>
                                                </th>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </th>
                                </tr>
                                </tbody>
                            </table>
                            <table class="spacer" style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                <tbody>
                                <tr style="padding: 0; text-align: left; vertical-align: top;">
                                    <td height="32x" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 32x; font-weight: normal; hyphens: auto; line-height: 32x; margin: 0; mso-line-height-rule: exactly; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">&nbsp;</td>
                                </tr>
                                </tbody>
                            </table>
                            <h6 class="text-center" style="Margin: 0; Margin-bottom: 10px; color: inherit; font-family: Helvetica, Arial, sans-serif; font-size: 18px; font-weight: normal; line-height: 1.3; margin: 0; margin-bottom: 10px; padding: 0; text-align: center; word-wrap: normal;"><strong><em>Check out our <a href="https://contractorcompliance.zendesk.com/hc/en-us" style="Margin: 0; color: #2199e8; font-family: Helvetica, Arial, sans-serif; font-weight: normal; line-height: 1.3; margin: 0; padding: 0; text-align: left; text-decoration: none;">Knowledge Base</a> for any FAQs that you may have on how to use our website.</em></strong></h6>
                            <table class="spacer" style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                <tbody>
                                <tr style="padding: 0; text-align: left; vertical-align: top;">
                                    <td height="16px" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 16px; margin: 0; mso-line-height-rule: exactly; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">&nbsp;</td>
                                </tr>
                                </tbody>
                            </table>
                            <h5 class="text-center" style="Margin: 0; Margin-bottom: 10px; color: inherit; font-family: Helvetica, Arial, sans-serif; font-size: 20px; font-weight: normal; line-height: 1.3; margin: 0; margin-bottom: 10px; padding: 0; text-align: center; word-wrap: normal;"><strong>Who Is Contractor Compliance?</strong></h5>
                            <table class="spacer" style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                <tbody>
                                <tr style="padding: 0; text-align: left; vertical-align: top;">
                                    <td height="16px" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 16px; margin: 0; mso-line-height-rule: exactly; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">&nbsp;</td>
                                </tr>
                                </tbody>
                            </table>
                            <table class="row" style="border-collapse: collapse; border-spacing: 0; display: table; padding: 0; position: relative; text-align: left; vertical-align: top; width: 100%;">
                                <tbody>
                                <tr style="padding: 0; text-align: left; vertical-align: top;">
                                    <th class="small-12 large-4 columns first" style="Margin: 0 auto; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 0 !important; padding-right: 0 !important; text-align: left; width: 33.33333%;">
                                        <center data-parsed="" style="min-width: none !important; width: 100%;">
                                            <table class="button secondary float-center" style="Margin: 0 0 16px 0; border-collapse: collapse; border-spacing: 0; float: none; margin: 0 0 16px 0; padding: 0; text-align: center; vertical-align: top; width: auto;">
                                                <tbody>
                                                <tr style="padding: 0; text-align: left; vertical-align: top;">
                                                    <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
                                                        <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                                            <tbody>
                                                            <tr style="padding: 0; text-align: left; vertical-align: top;">
                                                                <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; background: #0B6739; border: 0px solid #0B6739; border-collapse: collapse !important; color: #ffffff; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;"><a href="mailto:support@contractorcompliance.io" class="text-center" style="Margin: 0; border: 0 solid #0B6739; border-radius: 3px; color: #ffffff; display: inline-block; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: bold; line-height: 1.3; margin: 0; padding: 8px 16px 8px 16px; text-align: left; text-decoration: none;">Contact Us</a></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </center>
                                    </th>
                                    <th class="small-12 large-4 columns" style="Margin: 0 auto; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 0 !important; padding-right: 0 !important; text-align: left; width: 33.33333%;">
                                        <center data-parsed="" style="min-width: none !important; width: 100%;">
                                            <table class="button secondary float-center" style="Margin: 0 0 16px 0; border-collapse: collapse; border-spacing: 0; float: none; margin: 0 0 16px 0; padding: 0; text-align: center; vertical-align: top; width: auto;">
                                                <tbody>
                                                <tr style="padding: 0; text-align: left; vertical-align: top;">
                                                    <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
                                                        <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                                            <tbody>
                                                            <tr style="padding: 0; text-align: left; vertical-align: top;">
                                                                <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; background: #0B6739; border: 0px solid #0B6739; border-collapse: collapse !important; color: #ffffff; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;"><a href="mailto:support@contractorcompliance.io" class="text-center" style="Margin: 0; border: 0 solid #0B6739; border-radius: 3px; color: #ffffff; display: inline-block; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: bold; line-height: 1.3; margin: 0; padding: 8px 16px 8px 16px; text-align: left; text-decoration: none;">Forward Email</a></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </center>
                                    </th>
                                    <th class="small-12 large-4 columns last" style="Margin: 0 auto; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0 auto; padding: 0; padding-bottom: 16px; padding-left: 0 !important; padding-right: 0 !important; text-align: left; width: 33.33333%;">
                                        <center data-parsed="" style="min-width: none !important; width: 100%;">
                                            <table class="button secondary float-center" style="Margin: 0 0 16px 0; border-collapse: collapse; border-spacing: 0; float: none; margin: 0 0 16px 0; padding: 0; text-align: center; vertical-align: top; width: auto;">
                                                <tbody>
                                                <tr style="padding: 0; text-align: left; vertical-align: top;">
                                                    <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
                                                        <table style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
                                                            <tbody>
                                                            <tr style="padding: 0; text-align: left; vertical-align: top;">
                                                                <td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; background: #0B6739; border: 0px solid #0B6739; border-collapse: collapse !important; color: #ffffff; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; hyphens: auto; line-height: 1.3; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;"><a href="https://www.contractorcompliance.io/contractors" class="text-center" style="Margin: 0; border: 0 solid #0B6739; border-radius: 3px; color: #ffffff; display: inline-block; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: bold; line-height: 1.3; margin: 0; padding: 8px 16px 8px 16px; text-align: left; text-decoration: none;">Learn More</a></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </center>
                                    </th>
                                </tr>
                                </tbody>
                            </table>
                        </th>
                        <th class="expander" style="Margin: 0; color: #0a0a0a; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: normal; line-height: 1.3; margin: 0; padding: 0 !important; text-align: left; visibility: hidden; width: 0;"></th>
                    </tr>
                    </tbody>
                </table>
            </th>
        </tr>
        </tbody>
    </table>
@endsection
