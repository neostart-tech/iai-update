<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html style="margin: 0;padding: 0;" xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>{{ env('APP_NAME') }}</title>
	<!--[if !mso]><!-->
	<meta http-equiv="X-UA-Compatible" content="IE=edge" /><!--<![endif]-->
	<meta name="viewport" content="width=device-width" />
	<meta name="x-apple-disable-message-reformatting" />
	@include('mails._styles')
	<meta property="og:title" content="{{ $mailTitle ?? 'My First Campaign'}}" />
	<style>
		.btn-hover:hover {
			background-color: #6AA226 !important;
			transform: translateY(-2px);
			box-shadow: 0 10px 25px -5px rgba(128, 191, 46, 0.3) !important;
		}
		.icon-bg {
			background-color: #80BF2E !important;
			transition: transform 0.2s ease;
		}
		.icon-bg:hover {
			transform: scale(1.05);
		}
	</style>
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
								<!-- Icône SVG au lieu du logo -->
								<div class="logo-center" align="center" id="emb-email-header">
									<div class="icon-bg" style="display: inline-flex;align-items: center;justify-content: center;width: 80px;height: 80px;border-radius: 16px;background-color: #80BF2E;box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);">
										<svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M12 4C8.13401 4 5 7.13401 5 11V15C5 15.5523 4.55228 16 4 16C3.44772 16 3 15.5523 3 15V11C3 6.02944 7.02944 2 12 2C16.9706 2 21 6.02944 21 11V15C21 15.5523 20.5523 16 20 16C19.4477 16 19 15.5523 19 15V11C19 7.13401 15.866 4 12 4Z" fill="white"/>
											<path d="M8 15V11C8 8.79086 9.79086 7 12 7C14.2091 7 16 8.79086 16 11V15C16 15.5523 16.4477 16 17 16C17.5523 16 18 15.5523 18 15V11C18 7.68629 15.3137 5 12 5C8.68629 5 6 7.68629 6 11V15C6 15.5523 6.44772 16 7 16C7.55228 16 8 15.5523 8 15Z" fill="white"/>
											<path d="M12 10C10.8954 10 10 10.8954 10 12V16C10 17.1046 10.8954 18 12 18C13.1046 18 14 17.1046 14 16V12C14 10.8954 13.1046 10 12 10Z" fill="white"/>
										</svg>
									</div>
								</div>
							</div>
							<!--[if mso]></td></tr></table><![endif]-->
						</div>
					</div>
					<div>
						<div class="layout one-col fixed-width stack"
							style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;">
							<div class="layout__inner"
								style="border-collapse: collapse;display: table;width: 100%;background-color: #ffffff;border-radius: 16px;overflow: hidden;box-shadow: 0 20px 40px -15px rgba(0,0,0,0.15);">
								<!--[if mso]>
						<table align="center" cellpadding="0" cellspacing="0" role="presentation">
							<tr class="layout-fixed-width" style="background-color: #ffffff;">
								<td style="width: 600px" class="w560"><![endif]-->
								<div class="column"
									style="text-align: left;color: #787778;font-size: 16px;line-height: 24px;font-family: Ubuntu,sans-serif;">

									<!-- Ligne d'accent verte -->
									<div style="height: 4px; background-color: #80BF2E; width: 100%;"></div>

									<div style="Margin-left: 20px;Margin-right: 20px;Margin-top: 24px;">
										<div style="mso-line-height-rule: exactly;line-height: 20px;font-size: 1px;">&nbsp;</div>
									</div>

									<div style="Margin-left: 20px;Margin-right: 20px;">
										<div style="mso-line-height-rule: exactly;mso-text-raise: 11px;vertical-align: middle;">
											<!-- Icône de sécurité -->
											<div align="center" style="margin-bottom: 15px;">
												<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#80BF2E" stroke-width="1.5"/>
													<path d="M12 8V12M12 16H12.01" stroke="#80BF2E" stroke-width="2" stroke-linecap="round"/>
												</svg>
											</div>
											<h1 class="size-28 text-justify"
												style="Margin-top: 0;font-style: normal;font-weight: 300;color: #333333;font-size: 28px;line-height: 36px;text-align: center;letter-spacing: -0.5px;"
												lang="x-size-28">
												{{ $mailTitle ?? "Bienvenue au sein de l'administration de IAI-Togo" }}
											</h1>
											{!! $mailContent ?? "<h1>Mail content</h1>" !!}
										</div>
									</div>

									<div style="Margin-left: 20px;Margin-right: 20px;">
										<div style="mso-line-height-rule: exactly;line-height: 10px;font-size: 1px;">&nbsp;</div>
									</div>
									@isset($buttonText)
									<div style="Margin-left: 20px;Margin-right: 20px;">
										<div class="btn btn--flat" style="margin-bottom: 20px ;text-align: center;">
											<!-- Bouton avec icône SVG -->
											<a
												style="border-radius: 8px;display: inline-flex;align-items: center;justify-content: center;font-size: 14px;font-weight: 500;line-height: 24px;padding: 14px 28px;text-align: center;text-decoration: none !important;transition: all 0.2s ease-in;color: #ffffff !important;background-color: #80bf2e;font-family: Ubuntu, sans-serif;box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);"
												href="{{ $buttonHref ?? 'https://example.com' }}"
												class="btn-hover"
												onmouseover="this.style.backgroundColor='#6AA226';this.style.transform='translateY(-2px)';this.style.boxShadow='0 10px 25px -5px rgba(128, 191, 46, 0.3)';"
												onmouseout="this.style.backgroundColor='#80bf2e';this.style.transform='translateY(0)';this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)';">
												<!-- Icône cadenas SVG -->
												<svg class="mr-2" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 8px;">
													<path d="M12 2C9.23858 2 7 4.23858 7 7V9H6C4.89543 9 4 9.89543 4 11V19C4 20.1046 4.89543 21 6 21H18C19.1046 21 20 20.1046 20 19V11C20 9.89543 19.1046 9 18 9H17V7C17 4.23858 14.7614 2 12 2ZM12 4C13.6569 4 15 5.34315 15 7V9H9V7C9 5.34315 10.3431 4 12 4ZM12 14C10.8954 14 10 14.8954 10 16C10 17.1046 10.8954 18 12 18C13.1046 18 14 17.1046 14 16C14 14.8954 13.1046 14 12 14Z" fill="white"/>
												</svg>
												{{ $buttonText ?? "Cliquez ici pour finaliser la création de votre compte" }}
											</a>
											<!--[if mso]>
											<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="http://example.com"
												style="width:243.75pt" arcsize="9%" fillcolor="#80BF2E" stroke="f">
												<v:textbox style="mso-fit-shape-to-text:t" inset="0pt,8.25pt,0pt,8.25pt">
													<center
														style="font-size:14px;line-height:24px;color:#FFFFFF;font-family:Ubuntu,sans-serif;font-weight:bold;mso-line-height-rule:exactly;mso-text-raise:1.5px">
														{{ $buttonText ?? "Cliquez ici pour conclure votre inscription" }}
													</center>
												</v:textbox>
											</v:roundrect>
											<![endif]-->
										</div>
									</div>
									@endisset

									<!-- Lien direct avec icône -->
									<div style="Margin-left: 20px;Margin-right: 20px;Margin-top: 20px;">
										<div style="background-color: #f9f9f9; border-radius: 8px; padding: 15px; border: 1px solid #eaeaea;">
											<div style="display: flex; align-items: center; margin-bottom: 8px;">
												<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 8px;">
													<path d="M13.8284 10.1716C12.2663 8.60948 9.73367 8.60948 8.17157 10.1716L4.17157 14.1716C2.60948 15.7337 2.60948 18.2663 4.17157 19.8284C5.73367 21.3905 8.26633 21.3905 9.82843 19.8284L10.93 18.7269M10.1716 13.8284C11.7337 15.3905 14.2663 15.3905 15.8284 13.8284L19.8284 9.82843C21.3905 8.26633 21.3905 5.73367 19.8284 4.17157C18.2663 2.60948 15.7337 2.60948 14.1716 4.17157L13.072 5.27114" stroke="#80BF2E" stroke-width="2" stroke-linecap="round"/>
												</svg>
												<p style="Margin: 0; font-size: 13px; color: #666666;">Lien direct :</p>
											</div>
											<p style="Margin: 0; font-size: 11px; color: #999999; word-break: break-all; background-color: #ffffff; padding: 8px; border-radius: 4px; border: 1px solid #eaeaea; font-family: monospace;">
												{{ $buttonHref ?? 'https://example.com/reset-password/token' }}
											</p>
										</div>
									</div>

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

									<!-- Message de sécurité avec icône -->
									<div style="Margin-left: 20px;Margin-right: 20px;Margin-top: 15px; border-top: 1px solid #eaeaea; padding-top: 20px;">
										<div style="display: flex; align-items: flex-start;">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 10px; flex-shrink: 0;">
												<path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#80BF2E" stroke-width="1.5"/>
												<path d="M12 8V12M12 16H12.01" stroke="#80BF2E" stroke-width="2" stroke-linecap="round"/>
											</svg>
											<div>
												<p style="Margin: 0; font-size: 14px; color: #333333;">
													<strong>Demande non effectuée ?</strong>
													<span style="color: #666666;"> Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</span>
												</p>
											</div>
										</div>
									</div>

									<div style="Margin-left: 20px;Margin-right: 20px;Margin-bottom: 24px;">
										<div style="mso-line-height-rule: exactly;line-height: 5px;font-size: 1px;">&nbsp;</div>
									</div>

								</div>
								<!--[if mso]>
								</td>
							</tr>
							</table><![endif]-->
					</div>
				</div>

				<div style="mso-line-height-rule: exactly;line-height: 10px;font-size: 10px;">&nbsp;</div>

			</div>
			<div role="contentinfo">
				<div style="line-height:4px;font-size:4px;" id="footer-top-spacing">&nbsp;</div>
				<div class="layout email-flexible-footer email-footer"
						 style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;"
						 dir="rtl" id="footer-content">
					<div class="layout__inner right-aligned-footer" style="border-collapse: collapse;display: table;width: 100%;background-color: #f9f9f9; border-radius: 0 0 16px 16px;">
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
											 style="text-align: center;font-size: 12px;line-height: 19px;color: #787778;font-family: Ubuntu,sans-serif;width: 600px; padding: 20px 0;"
											 dir="ltr">
										<!-- Petites icônes dans le footer -->
										<div style="display: flex; justify-content: center; gap: 20px; margin-bottom: 15px;">
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #80BF2E;">
												<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
												<path d="M12 7V12L15 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
											</svg>
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #80BF2E;">
												<path d="M12 2L3 7V12C3 16.97 7.03 21 12 21C16.97 21 21 16.97 21 12V7L12 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="color: #80BF2E;">
												<path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="1.5"/>
												<path d="M22 6L12 13L2 6" stroke="currentColor" stroke-width="1.5"/>
											</svg>
										</div>
										<div style="margin-left: 0;margin-right: 0;Margin-top: 10px;Margin-bottom: 10px;">
											<div class="email-footer__additional-info"
													 style="font-size: 12px;line-height: 19px;margin-bottom: 5px;margin-top: 0px;">
												<div bind-to="address"><p class="email-flexible-footer__additionalinfo--center"
																									style="Margin-top: 0;Margin-bottom: 0; color: #999999;">{{ env('APP_NAME') }}, Lomé, Togo</p>
												</div>
											</div>
											<div class="email-footer__additional-info"
													 style="font-size: 11px;line-height: 19px;margin-bottom: 0;margin-top: 0px;">
												<div><p class="email-flexible-footer__additionalinfo--center" style="Margin-top: 0;Margin-bottom: 0; color: #bbbbbb;">Email automatique — Expire dans 24h</p></div>
											</div>
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