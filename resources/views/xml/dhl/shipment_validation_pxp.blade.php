<?xml version="1.0" encoding="UTF-8"?>
<req:ShipmentRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com ship-val-global-req.xsd" schemaVersion="6.2">
	<Request>
		<ServiceHeader>
			<MessageTime>{{$vars['date']}}</MessageTime>
			<MessageReference>{{$vars['reference']}}</MessageReference>
			<SiteID>v62_C15dKZGJec</SiteID>
			<Password>Alc5Z1Yjx5</Password>
		</ServiceHeader>
		<MetaData>
			<SoftwareName>3PV</SoftwareName>
			<SoftwareVersion>6.2</SoftwareVersion>
		</MetaData>
	</Request>
	<RegionCode>{{$vars['regionCode']}}</RegionCode>
	<NewShipper>N</NewShipper>
	<LanguageCode>en</LanguageCode>
	<PiecesEnabled>Y</PiecesEnabled>
	<Billing>
		<ShipperAccountNumber>{{$vars['accountNumber']}}</ShipperAccountNumber>
		<ShippingPaymentType>S</ShippingPaymentType>
		<BillingAccountNumber>{{$vars['accountNumber']}}</BillingAccountNumber>
		<DutyPaymentType>S</DutyPaymentType>
		<DutyAccountNumber>{{$vars['accountNumber']}}</DutyAccountNumber>
	</Billing>
	<Consignee>
		<CompanyName>{{$order->Name.' '.$order->Surname}}</CompanyName>
		<AddressLine>{{$order->DAddress1}}</AddressLine>
		<AddressLine>{{$order->DAddress2}}</AddressLine>
		<!-- <AddressLine></AddressLine> -->
		<City>{{$order->DAddress3}}</City>
		<!-- <Division></Division> -->
		<PostalCode>{{$order->DPostcode}}</PostalCode>
		<CountryCode>{{$order->CountryCodeName}}</CountryCode>
		<CountryName>{{$order->CountryName}}</CountryName>
		<!-- <FederalTaxId></FederalTaxId> -->
		<Contact>
			<PersonName>{{$order->Name.' '.$order->Surname}}</PersonName>
			<PhoneNumber>{{$vars['phone']}}</PhoneNumber>
			<!-- <PhoneExtension></PhoneExtension> -->
			<!-- <FaxNumber></FaxNumber> -->
			<!-- <Telex></Telex> -->
			<Email>{{$order->Email}}</Email>
		</Contact>
		<!-- <Suburb></Suburb> -->
	</Consignee>
	@if($vars['isDutiable'] == 'Y')
	<Dutiable>
		<DeclaredValue>{{ $vars['value'][1] }}</DeclaredValue>
		<DeclaredCurrency>{{ $vars['value'][0] }}</DeclaredCurrency>
		<ScheduleB></ScheduleB>
		<ExportLicense></ExportLicense>
		<ShipperEIN></ShipperEIN>
		<ShipperIDType>S</ShipperIDType>
		<ImportLicense></ImportLicense>
		<ConsigneeEIN></ConsigneeEIN>
		<TermsOfTrade>CIF</TermsOfTrade>
	</Dutiable>
	<UseDHLInvoice>Y</UseDHLInvoice>
	<DHLInvoiceLanguageCode>en</DHLInvoiceLanguageCode>
	<DHLInvoiceType>CMI</DHLInvoiceType>
	<ExportDeclaration>
		<SignatureName>Riaz Vali</SignatureName>
		<SignatureTitle>Director</SignatureTitle>
		<ExportReason>Sale</ExportReason>
		<ExportReasonCode>P</ExportReasonCode>
		<InvoiceNumber>{{$vars['reference']}}</InvoiceNumber>
		<InvoiceDate>{{ date("Y-m-d") }}</InvoiceDate>
		<BillToCompanyName>HR Healthcare Ltd</BillToCompanyName>
		<BillToContanctName>Yasin Ugharadar</BillToContanctName>
		<BillToAddressLine>Unit 18, Waters Meeting, Britannia Way</BillToAddressLine>
		<BillToCity>Bolton</BillToCity>
		<BillToPostcode>BL2 2HH</BillToPostcode>
		<BillToCountryName>United Kingdom</BillToCountryName>
		<BillToPhoneNumber>00441204559999</BillToPhoneNumber>
		<BillToFederalTaxID>{{ $vars['billedToVAT'] }}</BillToFederalTaxID>
		<Remarks>
			EU EORI: NL823615728
		</Remarks>
		@if(isset($vars['signature']))
		<SignatureImage>{{ $vars['signature'] }}</SignatureImage>
		@else
		<SignatureImage/>
		@endif
		<AddDeclText1>
			The exporter of the products covered by this document (EORI: GB275673564000) declares that, except where otherwise clearly indicated, these products are of United Kingdom preferential origin.		
		</AddDeclText1>
		<AddDeclText2>
			(Bolton, United Kingdom, {{ date("Y-m-d") }})
			(HR Healthcare Ltd)
		</AddDeclText2>
		{{--
		@if($order->DCountryCode == 75)		
		<AddDeclText3>
		@for($i = 0; $i < count($products); $i++)
			@if($i != 0 && $products[$i]->Note != $products[$i-1]->Note)
			TAUX REDUIT DE TVA selon AMM n°{{$products[$i]->Note}}
			@elseif($i != 0 && $products[$i]->Note == $products[$i-1]->Note)
			@else 
			TAUX REDUIT DE TVA selon AMM n°{{$products[$i]->Note}}
			@endif
		@endfor
		</AddDeclText3>
		@endif
		--}}
		@for($i = 0; $i < count($products); $i++)
		<ExportLineItem>
			<LineNumber>{{ $i+1 }}</LineNumber>
			<Quantity>1</Quantity>
			<QuantityUnit>BOX</QuantityUnit>
			<Description>{{ $products[$i]->Description }} {{ $products[$i]->Dosage }} {{ $products[$i]->Unit }}</Description> <!-- MEDICINE NAME -->
			<Value>{{ $vars['itemValues'][$i] }}</Value>
			<!-- <CommodityName>Medicine</CommodityName> -->
			<CommodityCode>{{ $products[$i]->TariffCode }}</CommodityCode>
			<Weight>
				<Weight>0.5</Weight>
				<WeightUnit>K</WeightUnit>
			</Weight>
			<GrossWeight>
				<Weight>0.5</Weight>
				<WeightUnit>K</WeightUnit>
			</GrossWeight>
			<ManufactureCountryCode>{{ $products[$i]->CountryCodeName }}</ManufactureCountryCode>
			<ManufactureCountryName>{{ $products[$i]->CountryName }}</ManufactureCountryName>
		</ExportLineItem>
		@endfor
	</ExportDeclaration>
	@endif
	<Reference>
		<ReferenceID>{{$order->PrescriptionID.'-'.$order->ReferenceNumber}}</ReferenceID>
		<!--<ReferenceType></ReferenceType>-->
	</Reference>
	<ShipmentDetails>
		<NumberOfPieces>1</NumberOfPieces>
		<Pieces>
			<Piece>
				<PieceID>1</PieceID>
				<PackageType>EE</PackageType>
				<Weight>0.5</Weight>
				<DimWeight>1.0</DimWeight>
				<Width>20</Width>
				<Height>20</Height>
				<Depth>10</Depth>
			</Piece>
		</Pieces>
		<Weight>0.5</Weight>
		<WeightUnit>K</WeightUnit>
		<GlobalProductCode>{{ $vars['globalProductCode'] }}</GlobalProductCode>
		<LocalProductCode>{{ $vars['localProductCode'] }}</LocalProductCode>
		<Date>{{ date("Y-m-d") }}</Date>
		<Contents>Medicine</Contents>
		<DoorTo>DD</DoorTo>
		<DimensionUnit>C</DimensionUnit>
		<!-- <InsuredAmount>20.00</InsuredAmount> -->
		<PackageType>EE</PackageType>
		<IsDutiable>{{ $vars['isDutiable'] }}</IsDutiable>
		<CurrencyCode>GBP</CurrencyCode>
	</ShipmentDetails>
	<Shipper>
		<ShipperID>{{ $vars['accountNumber'] }}</ShipperID>
		<CompanyName>HR Healthcare Ltd</CompanyName>
		<RegisteredAccount>{{ $vars['accountNumber'] }}</RegisteredAccount>
		<AddressLine>{{ $vars['shipper']->Address1 }}</AddressLine>
		<AddressLine>{{ $vars['shipper']->Address2 }}</AddressLine>
		<AddressLine>{{ $vars['shipper']->Address3 }}</AddressLine>
		<!-- <AddressLine></AddressLine> -->
		<City>{{ $vars['shipper']->Address4 }}</City>
		<!-- <Division></Division> -->
		<PostalCode>{{ $vars['shipper']->Postcode }}</PostalCode>
		<CountryCode>{{ $vars['shipper']->CountryCodeName }}</CountryCode>
		<CountryName>{{ $vars['shipper']->CountryName }}</CountryName>
        <FederalTaxId>{{ $vars['vat'] }}</FederalTaxId>
        <EORI_No>{{ $vars['eori'] }}</EORI_No>
		<Contact>
			<PersonName>Dispatch</PersonName>
			<PhoneNumber>{{ $vars['shipper']->Telephone }}</PhoneNumber>
			<!-- <PhoneExtension></PhoneExtension>
			<FaxNumber></FaxNumber>
			<Telex></Telex> -->
			<Email>adam@hrhealthcare.group</Email>
		</Contact>
	</Shipper>
	<SpecialService>
		<SpecialServiceType>WY</SpecialServiceType>
	</SpecialService>
	<SpecialService>
		<SpecialServiceType>DD</SpecialServiceType>
	</SpecialService>
	<Place>
		<ResidenceOrBusiness>B</ResidenceOrBusiness>
		<CompanyName>HR Healthcare Ltd</CompanyName>
		<AddressLine>{{ $vars['shipper']->Address1 }}</AddressLine>
		<AddressLine>{{ $vars['shipper']->Address3 }}</AddressLine>
		<City>{{ $vars['shipper']->Address4 }}</City>
		<CountryCode>{{ $vars['shipper']->CountryCodeName }}</CountryCode>
		<!-- <DivisionCode></DivisionCode>
		<Division></Division> -->
		<PostalCode>{{ $vars['shipper']->Postcode }}</PostalCode>
	</Place>
	<EProcShip>N</EProcShip>
	<!-- <LabelImageFormat>PDF</LabelImageFormat> -->
	<LabelImageFormat>ZPL2</LabelImageFormat>
	<RequestArchiveDoc>N</RequestArchiveDoc>
	<!-- <DocImages>
		<DocImage>
			<Type>CIN</Type>
			<Image>Base 64 encoded string</Image>
			<ImageFormat>PDF</ImageFormat>
		</DocImage>
	</DocImages> -->
	<Label>
		<HideAccount>N</HideAccount>
		<LabelTemplate>6X4_thermal</LabelTemplate>
	</Label>
</req:ShipmentRequest>