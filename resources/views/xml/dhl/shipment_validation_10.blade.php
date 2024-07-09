<req:ShipmentRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.dhl.com ship-val-global-req.xsd" schemaVersion="10.0">
    <Request>
        <ServiceHeader>
            <MessageTime>{{ $vars['date'] }}</MessageTime>
            <MessageReference>{{ $vars['reference'] }}</MessageReference>
            <SiteID>v62_C15dKZGJec</SiteID>
            <Password>Alc5Z1Yjx5</Password>
        </ServiceHeader>
        <MetaData>
            <SoftwareName>3PV</SoftwareName>
            <SoftwareVersion>10.0</SoftwareVersion>
        </MetaData>
    </Request>
    <RegionCode>{{ $vars['regionCode'] }}</RegionCode>
    <LanguageCode>en</LanguageCode>
    <Billing>
        <ShipperAccountNumber>{{ $vars['accountNumber'] }}</ShipperAccountNumber>
        <ShippingPaymentType>S</ShippingPaymentType>
        <BillingAccountNumber>{{ $vars['billingAccountNumber'] }}</BillingAccountNumber>
    </Billing>
    <Consignee>
        <CompanyName>{{ $order->Name . ' ' . $order->Surname }}</CompanyName>
        <AddressLine1>{{ $order->DAddress1 }}</AddressLine1>
        @if ($order->DAddress2)
            <AddressLine2>{{ $order->DAddress2 }}</AddressLine2>
        @endif
        <City>{{ $order->DAddress3 }}</City>
        <PostalCode>{{ $order->DPostcode }}</PostalCode>
        <CountryCode>{{ $order->CountryCodeName }}</CountryCode>
        <CountryName>{{ $order->CountryName }}</CountryName>
        <Contact>
            <PersonName>{{ $order->Name . ' ' . $order->Surname }}</PersonName>
            <PhoneNumber>{{ $vars['phone'] }}</PhoneNumber>
            <Email>{{ $order->Email }}</Email>
        </Contact>
    </Consignee>
    @if ($vars['isDutiable'] == 'Y')
        <Dutiable>
            <DeclaredValue>{{ $vars['value'][1] }}</DeclaredValue>
            <DeclaredCurrency>{{ $vars['value'][0] }}</DeclaredCurrency>
            <ScheduleB></ScheduleB>
            <ExportLicense></ExportLicense>
            <ImportLicense></ImportLicense>
            <TermsOfTrade>DAP</TermsOfTrade>
        </Dutiable>
    @endif
    <Reference>
        <ReferenceID>{{ $order->PrescriptionID . '-' . $order->ReferenceNumber }}</ReferenceID>
    </Reference>
    <ShipmentDetails>
        <Pieces>
            <Piece>
                <PieceID>1</PieceID>
                <PackageType>EE</PackageType>
                <Weight>0.5</Weight>
                <Width>20</Width>
                <Height>20</Height>
                <Depth>10</Depth>
            </Piece>
        </Pieces>
        <WeightUnit>K</WeightUnit>
        <GlobalProductCode>{{ $vars['globalProductCode'] }}</GlobalProductCode>
        <LocalProductCode>{{ $vars['localProductCode'] }}</LocalProductCode>
        <Date>{{ date('Y-m-d') }}</Date>
        <Contents>{{ $vars['shipper']->Contents }}</Contents>
        <DimensionUnit>C</DimensionUnit>
        <PackageType>EE</PackageType>
        <IsDutiable>{{ $vars['isDutiable'] }}</IsDutiable>
        <CurrencyCode>GBP</CurrencyCode>
    </ShipmentDetails>
    <Shipper>
        <ShipperID>{{ $vars['accountNumber'] }}</ShipperID>
        <CompanyName>{{ $vars['shipper']->ShipperName }}</CompanyName>
        <RegisteredAccount>{{ $vars['accountNumber'] }}</RegisteredAccount>
        <AddressLine1>{{ $vars['shipper']->Address1 }}</AddressLine1>
        @if ($vars['shipper']->Address2)
            <AddressLine2>{{ $vars['shipper']->Address2 }}</AddressLine2>
        @endif
        @if ($vars['shipper']->Address3)
            <AddressLine3>{{ $vars['shipper']->Address3 }}</AddressLine3>
        @endif
        <City>{{ $vars['shipper']->Address4 }}</City>
        <PostalCode>{{ $vars['shipper']->Postcode }}</PostalCode>
        <CountryCode>{{ $vars['shipper']->CountryCodeName }}</CountryCode>
        <CountryName>{{ $vars['shipper']->CountryName }}</CountryName>
        <Contact>
            <PersonName>Dispatch</PersonName>
            <PhoneNumber>{{ $vars['shipper']->Telephone }}</PhoneNumber>
            <Email>{{ $vars['shipper']->Email }}</Email>
        </Contact>
        <RegistrationNumbers>
            <RegistrationNumber>
                <Number>{{ $vars['vat'] }}</Number>
                <NumberTypeCode>VAT</NumberTypeCode>
                <NumberIssuerCountryCode>{{ $vars['shipper']->CountryCodeName }}</NumberIssuerCountryCode>
            </RegistrationNumber>
            <RegistrationNumber>
                <Number>{{ $vars['eori'] }}</Number>
                <NumberTypeCode>EOR</NumberTypeCode>
                <NumberIssuerCountryCode>{{ $vars['shipper']->CountryCodeName }}</NumberIssuerCountryCode>
            </RegistrationNumber>
        </RegistrationNumbers>
    </Shipper>
    <Place>
        <ResidenceOrBusiness>B</ResidenceOrBusiness>
        <CompanyName>{{ $vars['shipper']->ShipperName }}</CompanyName>
        <AddressLine1>{{ $vars['shipper']->Address1 }}</AddressLine1>
        @if ($vars['shipper']->Address2)
            <AddressLine2>{{ $vars['shipper']->Address2 }}</AddressLine2>
        @endif
        @if ($vars['shipper']->Address3)
            <AddressLine3>{{ $vars['shipper']->Address3 }}</AddressLine3>
        @endif
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
