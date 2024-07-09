export default {
    methods: {
        setupFilters(){
            this.filters.find((o, i) => {
                switch (o.value) {
                    case 'status':
                        o.options = this.orderStatusesSelect;
                        break;
                    case 'status-extended':
                        o.options = this.orderStatusesOptionsComputed;
                        break;
                    case 'doctor':
                        axios.get('/doctors')
                        .then((response) => {
                            response.data.data.forEach(doctor => {
                                o.options.push({
                                    label: doctor.Title + ' '+ doctor.Name + ' '+ doctor.Surname,
                                    id: doctor.DoctorID
                                });
                            });
                        })
                        .catch((error) => {
                            console.log(error);
                        })     
                        // o.options = this.orderStatusesSelect;
                        break;                         
                    case 'country':
                        axios.get('/countries')
                        .then((response) => {
                            response.data.data.forEach(country => {
                                if(country.CountryID != 1 && country.CountryID != 244 && country.CountryID != 245){
                                    o.options.push({
                                        label: country.Name,
                                        id: country.CountryID
                                    });
                                } else if(country.CountryID == 1){
                                    o.options.push({
                                        label: country.Name,
                                        id: country.CountryID,
                                        children: [ 
                                            {
                                                id: '1-northern-ireland',
                                                label: 'Northern Ireland',
                                                customLabel: `United Kingdom - Northern Ireland`
                                            }, 
                                            {
                                                id: '1-great-britain',
                                                label: 'Great Britain',
                                                customLabel: `United Kingdom - Great Britain`
                                            },
                                            {
                                                id: 244,
                                                label: 'Jersey',
                                                customLabel: `United Kingdom - Jersey`
                                            },
                                            {
                                                id: 245,
                                                label: 'Guernsey',
                                                customLabel: `United Kingdom - Guernsey`
                                            },
                                        ]                                        
                                    });
                                }
                            });
                        })
                        .catch((error) => {
                            console.log(error);
                        })                          
                        // o.options = this.orderStatusesSelect;
                        break;
                    case 'product':
                        axios.get('/products')
                        .then((response) => {
                            response.data.data.forEach(product => {
                                o.options.push({
                                    title: product.Name,
                                    value: product.Code
                                });
                                // o.options.push({
                                //     label: product.Name,
                                //     id: product.Code
                                // });                                
                            });
                        })
                        .catch((error) => {
                            console.log(error);
                        })                          
                        // o.options = this.orderStatusesSelect;
                        break;                        
                    case 'delivery':
                        axios.get('/delivery-companies')
                        .then((response) => {
                            response.data.data.forEach(company => {
                                o.options.push({
                                    label: company.Name,
                                    id: company.SettingID
                                });
                            });
                        })
                        .catch((error) => {
                            console.log(error);
                        })     
                        // o.options = this.orderStatusesSelect;
                        break;
                    case 'pharmacy':
                        axios.get('/pharmacies/list')
                        .then((response) => {
                            console.log("in filterData");
                            response.data.data.forEach(pharmacy => {
                                o.options.push({
                                    label: pharmacy.Title,
                                    id: company.PharmacyID
                                });
                            });
                        })
                        .catch((error) => {
                            console.log(error);
                        })     
                        // o.options = this.orderStatusesSelect;
                        break;
                    case 'client':
                        axios.get('/clients')
                        .then((response) => {
                            response.data.data.forEach(client => {
                                o.options.push({
                                    label: client.CompanyName,
                                    id: client.ClientID
                                });
                            });
                        })
                        .catch((error) => {
                            console.log(error);
                        })     
                        // o.options = this.orderStatusesSelect;
                        break;                          
                    default:
                        break;
                }      
            });      
        }
    },
}