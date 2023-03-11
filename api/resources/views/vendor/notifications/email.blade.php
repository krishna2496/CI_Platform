<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Optimy</title>
	<meta charset="UTF-8" />
	<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
	<meta http-equiv="x-ua-compatible" content="IE=edge"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>

	<style type="text/css">
		body{margin:0;padding:0; font-family: Verdana,Geneva,sans-serif; background: #f2f2f2; font-size: 12px; color: #414141;}
		a{border:0;outline:none;text-decoration:none;cursor: pointer;}
		a,
		a:hover{transition:all .3s;-o-transition:all .3s;-ms-transition:all .3s;-moz-transition:all .3s;-webkit-transition:all .3s;}
		img{border:0;outline:none; }

		@media only screen and (max-width:600px) {
			.main-table {width: 90%;}
			.inner-table {width: 80%;}
			.title_text  {font-size: 25px !important; line-height: 35px !important;}
		}
		@media only screen and (max-width:374px) {
			.inner-table {width: 90%;}
			.button_text { color:#f88634; font-size:17px; font-family: Verdana,Geneva,sans-serif;}
			
		}
	</style>
</head>
@php
$companyLogo =  (config('app.tenant_logo') != '') ? config('app.tenant_logo') : url('/images/optimy_logo.png');
$resetArowPasswordBtn = url('/images/arrow.png');
$token =explode('/',$actionUrl);
$token = end($token);
$actionUrl = config('app.mail_url').$token;
@endphp
<body>
	<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f2f2f2" align="center" style="background:#f2f2f2;">
		<tr>
			<td height="70" style="font-size:0; line-height:0;" align="left" valign="top">
			</td>
		</tr>
		<tr>
			<td align="center" valign="top">
				<table width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#FFF" style="background:#FFFFFF;" class="main-table">
					<tr>
						<td height="3" style="background:#757575;" align="left" valign="top">
						</td>
					</tr>
					<tr>
						<td height="50" style="font-size:0; line-height:0;" align="left" valign="top">
						</td>
					</tr>
					<tr>
						<td align="center" valign="top">
							<table width="500" cellpadding="0" cellspacing="0" border="0" bgcolor="#FFF" style="background:#FFFFFF;" class="inner-table">
								<tr>
									<td>
										<img src="{{ $companyLogo }}" height="50" width="150"  alt="Logo" />
									</td>
								</tr>
								<tr>
									<td height="40" style="font-size:0; line-height:0;">

									</td>
								</tr>
								<tr>
									<td style="font-family: Verdana,Geneva,sans-serif;  color: #3a3a3a; font-size:30px; line-height: 46px;" class="title_text">
									{{ trans('mail.forgot_password.HEADING_ONE', [], config('app.user_language_code')) }} <br/>
									{{ trans('mail.forgot_password.HEADING_TWO', [], config('app.user_language_code')) }}
									</td>
								</tr>
								<tr>
									<td height="15" style="font-size:0; line-height:0;">
									</td>
								</tr>
								<tr>
									<td style="font-family: Verdana,Geneva,sans-serif;  color: #414141; font-size:15px; line-height: 19px;">
										{{ trans('mail.forgot_password.PARAGRAPH_ONE', [], config('app.user_language_code')) }}<br/>
									</td>
								</tr>
								<tr>
									<td height="25" style="font-size:0; line-height:0;">
									</td>
								</tr>
								
								<tr>
									<td>
										<table cellpadding="0" cellspacing="0" border="0" class="button" style="border: 2px solid #f88634; border-radius:50px; -ms-border-radius:50px; -moz-border-radius:50px; -webkit-border-radius:50px; border-radius: 24px; color:#f88634; font-size:17px; background-color: #ffffff; display:inline-block;">
											<tr>
												<td style=" width:20px;"></td>
												<td class="button_text" style="color:#f88634; font-size:17px; font-family: Verdana,Geneva,sans-serif;" valign="middle">
													<a href="{{ $actionUrl }}" title="{{ trans('mail.forgot_password.RESET_PASSWORD_BUTTON', [], config('app.user_language_code')) }}"  style="display:inline-block; color:#f88634; font-size:17px; vertical-align:middle; display: block;">
														{{ trans('mail.forgot_password.RESET_PASSWORD_BUTTON', [], config('app.user_language_code')) }}
													</a>
												</td>
												<!-- <td>
													<a href="{{ $actionUrl }}" title="{{ trans('mail.forgot_password.RESET_PASSWORD_BUTTON', [], config('app.user_language_code')) }}"  style="display:inline-block; color:#f88634; font-size:17px; vertical-align: top;">
														<img src="{{ $resetArowPasswordBtn }} " height="42" width="30" alt="Arrow" />
													</a>
												</td> -->
												<td style=" width: 20px;"></td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td height="25" style="font-size:0; line-height:0;">
									</td>
								</tr>
								<tr>
									<td style="font-family: Verdana,Geneva,sans-serif;  color: #414141; font-size:15px; line-height: 19px;">
									{{ trans('mail.forgot_password.PARAGRAPH_TWO', [], config('app.user_language_code')) }}
									</td>
								</tr>
								<tr>
									<td height="45" style="font-size:0; line-height:0;">
									</td>
								</tr>
								<tr>
									<td height="1" style="background:#e8e8e8;">

									</td>
								</tr>
								<tr>
									<td height="45" style="font-size:0; line-height:0;">

									</td>
								</tr>
								<tr>
									<td style="font-family: Verdana,Geneva,sans-serif;  color: #757575; font-size:13px; line-height: 17px;">
										{{ trans('mail.forgot_password.FOOTER_TEXT', [], config('app.user_language_code')) }}
									</td>
								</tr>
								<tr>
									<td height="5" style="font-size:0; line-height:0;">
									</td>
								</tr>
							    <tr>
									<td style="font-family: Verdana,Geneva,sans-serif;  color: #074bbc; font-size:13px; line-height: 17px;">
										<a href="{{ $actionUrl }}" title="{{ $actionUrl }}" style="font-family: Verdana,Geneva,sans-serif;  color: #074bbc; font-size:13px; line-height: 17px;">
											{{ $actionUrl }}
										</a>

									</td>
								</tr>
								<tr>
									<td height="45" style="font-size:0; line-height:0;"></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td align="center" valign="top">
				<table width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#FFF" style="background:#FFFFFF;" class="main-table">
					<tr>
						<td  style="border-top:3px solid #757575; font-size:0; line-height:0;"></td>
					</tr>
					<tr>
						<td height="40" style="font-size:0; line-height:0;">
						</td>
					</tr>

					<tr>
						<td style="font-family: Verdana,Geneva,sans-serif;  color: #757575; font-size:13px; line-height: 17px; align:center; text-align:center">
							© {{date('Y')}} Optimy, {{ trans('mail.other_text.ALL_RIGHTS_RESERVED', [], config('app.user_language_code')) }}
						</td>
					</tr>
					<tr>
						<td height="40" style="font-size:0; line-height:0;">
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td height="70" style="font-size:0; line-height:0;">
			</td>
		</tr> 
	</table>
</body>
</html>