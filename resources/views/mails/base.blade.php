<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html style="margin: 0;padding: 0;" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>{{ env('APP_NAME') }}</title>
	<!--[if !mso]><!-->
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/><!--<![endif]-->
	<meta name="viewport" content="width=device-width"/>
	<meta name="x-apple-disable-message-reformatting"/>
	@include('mails._styles')
	<meta property="og:title" content="{{ $mailTitle ?? "My First Campaign"}}"/>
</head>
<!--[if mso]>
<body class="mso">
<![endif]-->
<!--[if !mso]><!-->
<body class="main full-padding" style="margin: 0;padding: 0;-webkit-text-size-adjust: 100%;">
<!--<![endif]-->
<table class="wrapper"
			 style="border-collapse: collapse;table-layout: fixed;min-width: 320px;width: 100%;background-color: #f0f0f0;"
			 cellpadding="0" cellspacing="0" role="presentation">
	<tbody>
	<tr>
		<td>
			<div role="banner">
				<div class="preheader"
						 style="Margin: 0 auto;max-width: 560px;min-width: 280px; width: 280px;width: calc(28000% - 167440px);">
					<div style="border-collapse: collapse;display: table;width: 100%;">
						<!--[if mso]>
						<table align="center" class="preheader" cellpadding="0" cellspacing="0" role="presentation">
							<tr>
								<td style="width: 280px" valign="top"><![endif]-->
						<div class="snippet"
								 style="display: table-cell;Float: left;font-size: 12px;line-height: 19px;max-width: 280px;min-width: 140px; width: 140px;width: calc(14000% - 78120px);padding: 10px 0 5px 0;color: #787778;font-family: Ubuntu,sans-serif;">

						</div>
						<!--[if mso]></td>
						<td style="width: 280px" valign="top"><![endif]-->
						<div class="webversion"
								 style="display: table-cell;Float: left;font-size: 12px;line-height: 19px;max-width: 280px;min-width: 139px; width: 139px;width: calc(14100% - 78680px);padding: 10px 0 5px 0;text-align: right;color: #787778;font-family: Ubuntu,sans-serif;">

						</div>
						<!--[if mso]></td></tr></table><![endif]-->
					</div>
				</div>
				<div class="header"
						 style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);"
						 id="emb-email-header-container">
					<!--[if mso]>
					<table align="center" class="header" cellpadding="0" cellspacing="0" role="presentation">
						<tr>
							<td style="width: 600px"><![endif]-->
					<div class="logo emb-logo-margin-box"
							 style="font-size: 26px;line-height: 32px;Margin-top: 6px;Margin-bottom: 20px;color: #c3ced9;font-family: Roboto,Tahoma,sans-serif;Margin-left: 20px;Margin-right: 20px;"
							 align="center">
						<div class="logo-center" align="center" id="emb-email-header"><img
								style="display: block;height: auto;width: 100%;border: 0;max-width: 179px;"
								src="{{ asset(config('images.mails.default'), true) }}" alt="Logo"
								width="179"/></div>
					</div>
					<!--[if mso]></td></tr></table><![endif]-->
				</div>
			</div>
			<div>
				<div class="layout one-col fixed-width stack"
						 style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;">
					<div class="layout__inner"
							 style="border-collapse: collapse;display: table;width: 100%;background-color: #ffffff;">
						<!--[if mso]>
						<table align="center" cellpadding="0" cellspacing="0" role="presentation">
							<tr class="layout-fixed-width" style="background-color: #ffffff;">
								<td style="width: 600px" class="w560"><![endif]-->
						<div class="column"
								 style="text-align: left;color: #787778;font-size: 16px;line-height: 24px;font-family: Ubuntu,sans-serif;">

							<div style="Margin-left: 20px;Margin-right: 20px;Margin-top: 24px;">
								<div style="mso-line-height-rule: exactly;line-height: 20px;font-size: 1px;">&nbsp;</div>
							</div>

							<div style="Margin-left: 20px;Margin-right: 20px;">
								<div style="mso-line-height-rule: exactly;mso-text-raise: 11px;vertical-align: middle;">
									<h1 class="size-28 text-justify"
											style="Margin-top: 0;font-style: normal;font-weight: normal;color: #565656;font-size: 24px;line-height: 32px;text-align: center;"
											lang="x-size-28">
										<strong>{{ $mailTitle ?? "Bienvenue au sein de l'administration de IAI-Togo" }}</strong></h1>
									{!! $mailContent ?? "<h1>Mail content</h1>" !!}
								</div>
							</div>

							<div style="Margin-left: 20px;Margin-right: 20px;">
								<div style="mso-line-height-rule: exactly;line-height: 10px;font-size: 1px;">&nbsp;</div>
							</div>
							@isset($buttonText)
								<div style="Margin-left: 20px;Margin-right: 20px;">
									<div class="btn btn--flat	" style="margin-bottom: -40px ;text-align: center;">
										<a
											style="border-radius: 4px;display: inline-block;font-size: 14px;font-weight: bold;line-height: 24px;padding: 12px 24px;text-align: center;text-decoration: none !important;transition: opacity 0.1s ease-in;color: #ffffff !important;background-color: #80bf2e;font-family: Ubuntu, sans-serif;"
											href="{{ $buttonHref ?? 'https://example.com' }}">{{ $buttonText ?? 'Cliquez ici pour finaliser la création de votre compte' }}</a>
										<!--<![endif]-->
										<p style="line-height:0;margin:0;">&nbsp;</p>
										<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="http://example.com"
																 style="width:243.75pt" arcsize="9%" fillcolor="#80BF2E" stroke="f">
											<v:textbox style="mso-fit-shape-to-text:t" inset="0pt,8.25pt,0pt,8.25pt">
												<center
													style="font-size:14px;line-height:24px;color:#FFFFFF;font-family:Ubuntu,sans-serif;font-weight:bold;mso-line-height-rule:exactly;mso-text-raise:1.5px">
													Cliquez ici pour conclure votre inscription
												</center>
											</v:textbox>
										</v:roundrect>
										<![endif]-->
									</div>
								</div>
							@endisset

							<div style="Margin-left: 20px;Margin-right: 20px;">
								<div style="mso-line-height-rule: exactly;line-height: 10px;font-size: 1px;">&nbsp;</div>
							</div>

							<div style="Margin-left: 20px;Margin-right: 20px;">
								<div style="mso-line-height-rule: exactly;mso-text-raise: 11px;vertical-align: middle;">
									<p style="Margin-top: 0;Margin-bottom: 20px;">
										<em>{!! $moreInfo ?? '' !!}<em/>
									</p>
								</div>
							</div>

							<div style="Margin-left: 20px;Margin-right: 20px;Margin-bottom: 24px;">
								<div style="mso-line-height-rule: exactly;line-height: 5px;font-size: 1px;">&nbsp;</div>
							</div>

						</div>
						<!--[if mso]></td></tr></table><![endif]-->
					</div>
				</div>

				<div style="mso-line-height-rule: exactly;line-height: 10px;font-size: 10px;">&nbsp;</div>

			</div>
			<div role="contentinfo">
				<div style="line-height:4px;font-size:4px;" id="footer-top-spacing">&nbsp;</div>
				<div class="layout email-flexible-footer email-footer"
						 style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;"
						 dir="rtl" id="footer-content">
					<div class="layout__inner right-aligned-footer" style="border-collapse: collapse;display: table;width: 100%;">
						<!--[if mso]>
						<table align="center" cellpadding="0" cellspacing="0" role="presentation">
							<tr class="layout-email-footer"><![endif]-->
						<!--[if mso]>
						<td>
							<table cellpadding="0" cellspacing="0"><![endif]-->
						<!--[if mso]>
						<td valign="top"><![endif]-->
						<div class="column"
								 style="text-align: right;font-size: 12px;line-height: 19px;color: #787778;font-family: Ubuntu,sans-serif;display: none;"
								 dir="ltr">
							<div class="footer-logo emb-logo-margin-box"
									 style="font-size: 26px;line-height: 32px;Margin-top: 6px;Margin-bottom: 20px;color: #7b663d;font-family: Roboto,Tahoma,sans-serif;"
									 align="center">
								<div emb-flexible-footer-logo align="center"></div>
							</div>
						</div>
						<!--[if mso]></td><![endif]-->
						<!--[if mso]>
						<td valign="top" class="w60"><![endif]-->
						<div class="column"
								 style="text-align: right;font-size: 12px;line-height: 19px;color: #787778;font-family: Ubuntu,sans-serif;display: none;"
								 dir="ltr">
							<div style="margin-left: 0;margin-right: 0;Margin-top: 10px;Margin-bottom: 10px;">
								<div class="footer__share-button">


								</div>
							</div>
						</div>
						<!--[if mso]></td><![endif]-->
						<!--[if mso]>
						<td valign="top" class="w260"><![endif]-->
						<table style="border-collapse: collapse;table-layout: fixed;display: inline-block;width: 600px;"
									 cellpadding="0" cellspacing="0">
							<tbody>
							<tr>
								<td>
									<div class="column js-footer-additional-info"
											 style="text-align: right;font-size: 12px;line-height: 19px;color: #787778;font-family: Ubuntu,sans-serif;width: 600px;"
											 dir="ltr">
										<div style="margin-left: 0;margin-right: 0;Margin-top: 10px;Margin-bottom: 10px;">
											<div class="email-footer__additional-info"
													 style="font-size: 12px;line-height: 19px;margin-bottom: 18px;margin-top: 0px;">
												<div bind-to="address"><p class="email-flexible-footer__additionalinfo--center"
																									style="Margin-top: 0;Margin-bottom: 0;">{{ env('APP_NAME') }}, Lom&#233;,
														Togo</p>
												</div>
											</div>
											<div class="email-footer__additional-info"
													 style="font-size: 12px;line-height: 19px;margin-bottom: 18px;margin-top: 0px;">
												<div><p class="email-flexible-footer__additionalinfo--center"
																style="Margin-top: 0;Margin-bottom: 0;"><a href="https://new.iai-togo.tg"
																																					 target="_blank">Site Officiel</a></p></div>
											</div>

											{{--											<div class="email-footer__additional-info"--}}
{{--													 style="font-size: 12px;line-height: 19px;margin-bottom: 15px;">--}}
{{--												<unsubscribe style="text-decoration: underline;" lang="en">Unsubscribe</unsubscribe>--}}
{{--											</div>--}}
											<!--[if mso]>&nbsp;<![endif]-->
										</div>
									</div>
								</td>
							</tr>
							</tbody>
						</table>
					
					</div>
				</div>
				<div style="line-height:40px;font-size:40px;" id="footer-bottom-spacing">&nbsp;</div>
			</div>

		</td>
	</tr>
	</tbody>
</table>
</body>
</html>