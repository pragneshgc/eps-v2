<?xml version="1.0" encoding="UTF-8"?>
<req:ShipmentRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com ship-val-global-req.xsd" schemaVersion="2.0">
    <Request>
        <ServiceHeader>
            <MessageTime>{{$vars['date']}}</MessageTime>
            <MessageReference>{{$vars['reference']}}</MessageReference>
            <SiteID>v62_C15dKZGJec</SiteID>
            <Password>Alc5Z1Yjx5</Password>
        </ServiceHeader>
    </Request>
    <RegionCode>{{$vars['regionCode']}}</RegionCode>
    <ShipmentDetails>
        <GlobalProductCode>{{ $vars['globalProductCode'] }}</GlobalProductCode>
        <LocalProductCode>{{ $vars['localProductCode'] }}</LocalProductCode>
        <Date>{{ date("Y-m-d") }}</Date>
    </ShipmentDetails>
    <Shipper>
        <OriginServiceAreaCode>MAN</OriginServiceAreaCode>
        <OriginFacilityCode>MAN</OriginFacilityCode>
        <CountryCode>GB</CountryCode>
    </Shipper>
    <Airwaybill>{{ $vars['airwayBillNumber'] }}</Airwaybill>
    <DocImages>
        <DocImage>
            <Type>DCL</Type>
            <Image>{{ $vars['pdf'] }}</Image>
            <ImageFormat>PDF</ImageFormat>
        </DocImage>
    </DocImages>
</req:ShipmentRequest>