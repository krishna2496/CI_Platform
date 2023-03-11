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
										<img src="{{ $data['logo'] }}" height="50" width="150"  alt="Logo" />
									</td>
								</tr>
								<tr>
									<td height="40" style="font-size:0; line-height:0;">

									</td>
								</tr>
								<tr>
									<td style="font-family: Verdana,Geneva,sans-serif;  color: #414141; font-size:15px; line-height: 19px;">
										{{ $data['fromUserName'] }} {{ trans('mail.recommonded_mission.HAS_RECOMMENDED_A_MISSION_TO_YOU', [], $data['colleagueLanguage']) }} 
									</td>
								</tr>
								<tr>
									<td height="15" style="font-size:0; line-height:0;">
									</td>
								</tr>
								
								<tr>
									<td style="font-family: Verdana,Geneva,sans-serif;  color: #3a3a3a; font-size:20px; line-height: 36px;" class="title_text">
										{{ trans('mail.recommonded_mission.MISSION', [], $data['colleagueLanguage']) }}  {{ $data['missionName'] }} <br/>
									</td>
								</tr>
								
								<tr>
									<td height="25" style="font-size:0; line-height:0;">
									</td>
								</tr>
																
								<tr>
									<td height="25" style="font-size:0; line-height:0;">
									</td>
								</tr>
								
								<tr>
									<td height="45" style="font-size:0; line-height:0;">

									</td>
								</tr>
								<tr>
									<td height="5" style="font-size:0; line-height:0;">
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
							© {{date('Y')}} Optimy, {{ trans('mail.recommonded_mission.ALL_RIGHTS_RESERVED', [], $data['colleagueLanguage']) }}
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