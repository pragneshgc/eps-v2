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
		<DutyPaymentType>R</DutyPaymentType>
	</Billing>
	<Consignee>
		<CompanyName>{{$order->Name.' '.$order->Surname}}</CompanyName>
		<AddressLine>{{$order->DAddress1}}</AddressLine>
		<AddressLine>{{$order->DAddress2}}</AddressLine>
		<City>{{$order->DAddress3}}</City>
		<PostalCode>{{$order->DPostcode}}</PostalCode>
		<CountryCode>{{$order->CountryCodeName}}</CountryCode>
		<CountryName>{{$order->CountryName}}</CountryName>
		<Contact>
			<PersonName>{{$order->Name.' '.$order->Surname}}</PersonName>
			<PhoneNumber>{{$vars['phone']}}</PhoneNumber>
			<Email>{{$order->Email}}</Email>
		</Contact>
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
		<TermsOfTrade>DAP</TermsOfTrade>
	</Dutiable>
	@endif
	<Reference>
		<ReferenceID>{{$order->PrescriptionID.'-'.$order->ReferenceNumber}}</ReferenceID>
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
		<Contents>Prescription Medicine</Contents>
		<DoorTo>DD</DoorTo>
		<DimensionUnit>C</DimensionUnit>
		<PackageType>EE</PackageType>
		<IsDutiable>{{ $vars['isDutiable'] }}</IsDutiable>
		<CurrencyCode>GBP</CurrencyCode>
	</ShipmentDetails>
	<Shipper>
		<ShipperID>{{ $vars['accountNumber'] }}</ShipperID>
		<CompanyName>Harrison Healthcare Ltd</CompanyName>
		<RegisteredAccount>{{ $vars['accountNumber'] }}</RegisteredAccount>
		<AddressLine>{{ $vars['shipper']->Address1 }}</AddressLine>
		<AddressLine>{{ $vars['shipper']->Address3 }}</AddressLine>
		<City>{{ $vars['shipper']->Address4 }}</City>
		<PostalCode>{{ $vars['shipper']->Postcode }}</PostalCode>
		<CountryCode>{{ $vars['shipper']->CountryCodeName }}</CountryCode>
		<CountryName>{{ $vars['shipper']->CountryName }}</CountryName>
        <FederalTaxId>{{ $vars['vat'] }}</FederalTaxId>
        <EORI_No>{{ $vars['eori'] }}</EORI_No>
		<Contact>
			<PersonName>Dispatch</PersonName>
			<PhoneNumber>{{ $vars['shipper']->Telephone }}</PhoneNumber>
			<Email>adam@hrhealthcare.group</Email>
		</Contact>
	</Shipper>
	<Place>
		<ResidenceOrBusiness>B</ResidenceOrBusiness>
		<CompanyName>Harrison Healthcare Ltd</CompanyName>
		<AddressLine>{{ $vars['shipper']->Address1 }}</AddressLine>
		<AddressLine>{{ $vars['shipper']->Address3 }}</AddressLine>
		<City>{{ $vars['shipper']->Address4 }}</City>
		<CountryCode>{{ $vars['shipper']->CountryCodeName }}</CountryCode>
		<PostalCode>{{ $vars['shipper']->Postcode }}</PostalCode>
	</Place>
	<EProcShip>N</EProcShip>
	<LabelImageFormat>ZPL2</LabelImageFormat>
	<RequestArchiveDoc>N</RequestArchiveDoc>
	<Label>
		<HideAccount>N</HideAccount>
		<LabelTemplate>6X4_thermal</LabelTemplate>
	</Label>
</req:ShipmentRequest>