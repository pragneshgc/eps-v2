<template>

    <!--Grid column-->
    <div class=" natcol-table" :class="[columnClass]">
        <transition name="fade">
            <div class="loader" v-show="loading">Loading...</div>
        </transition>

        <!--Card-->
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-12">
                        <h5>{{ tableTitle }}</h5>
                    </div>
                </div>
                <div class="row filters-row">
                    <div class="col-md-3">
                        <!--<form>-->
                            <div class="form-group">
                                <input v-model="queryString" type="text" class="form-control tBoxSize02" id="queryString" placeholder="Search...">
                            </div>      
                        <!--</form>-->
                    </div>
                    <div class="col-md-3">
                        <v-select v-model="limit" :options="limitOptions"></v-select>
                    </div>
                    <div v-if="filters" class="col-md-3">
                        <v-select placeholder="Select Filters" multiple v-model="selectedFilters" :options="filters"></v-select>
                    </div>
                    <div class="col-md-3">
                        <div class="dropdown float-right">
                            <a class="btn btn-primary waves-effect" v-on:click="printChart($el)"><i class="fa fa-print" aria-hidden="true"></i></a>
                            <a class="btn btn-primary waves-effect" v-on:click="exportPDF($el)"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></a>
                            <a v-if="csvUrl" class="btn btn-primary waves-effect" :href="csvUrl"><i class="fa fa-file" aria-hidden="true"></i></a>
                            <a v-else class="btn btn-primary waves-effect" v-on:click="exportCSV(data.data, tableTitle)"><i class="fa fa-file" aria-hidden="true"></i></a>                            
                            <!--<a class="btn btn-primary waves-effect" v-on:click="toggleFilters()"><i class="fa fa-cog" aria-hidden="true"></i></a>-->
                        </div>
                    </div>
                </div>
            </div>
            <!--Card content-->
            <div class="card-body">
                <!-- Table  -->
                <table v-show="data.data.length >= 1" class="table table-hover">
                    <!-- Table head -->
                    <thead class="primary-color text-white">
                        <tr>
                            <th 
                            class="clickable" 
                            v-for="(value, key) in data.data[0]" 
                            v-if="typeof hiddenColumns != 'undefined' ? !hiddenColumns.includes(key) : true" 
                            v-on:click="setOrder(key)"
                            >
                                {{ key }}
                                <i v-if="key == orderBy && orderDirection == 'DESC'" class="fa fa-caret-down"></i>
                                <i v-if="key == orderBy && orderDirection == 'ASC'" class="fa fa-caret-up"></i>
                                <i v-if="key != orderBy" class="fa fa-sort"></i>
                            </th>
                            <th v-if="redirectId || editId || deleteId">Tools</th>
                        </tr>
                    </thead>
                    <!-- Table head -->

                    <!-- Table body -->
                    <!--<transition-group tag="tbody" name="table-row">-->
                    <tbody>
                        <tr v-on:dblclick="redirect(item[redirectId])" v-for="item in data.data" :key="item[Object.keys(item)[0]]">
                            <td v-for="(value, key) in item" v-if="typeof hiddenColumns != 'undefined' ? !hiddenColumns.includes(key) : true">
                                {{ value }}
                            </td>
                            <td  v-if="redirectId || editId || deleteId">
                                <a 
                                v-if="redirectId"
                                class="btn btn-blue-grey waves-effect" 
                                v-on:click="redirect(item[redirectId])"
                                v-bind:class="{ 'clickable': redirectId}"
                                >
                                <i class="fa fa-search-plus" aria-hidden="true"></i>
                                </a>
                                <a 
                                v-if="editId"
                                class="btn btn-blue waves-effect" 
                                v-on:click="edit(item[editId])"
                                v-bind:class="{ 'clickable': editId}"
                                >
                                <i class="fa fa-pencil-square-o" aria-hidden="true"></i>   
                                </a>
                                <a 
                                v-if="deleteId"
                                class="btn btn-red waves-effect" 
                                v-on:click="deleteItem(item[deleteId])"
                                v-bind:class="{'clickable': deleteId}"
                                >
                                <i class="fa fa-trash" aria-hidden="true"></i>   
                                </a>                                
                            </td>                      
                        </tr>
                    </tbody>
                    <!--</transition-group>-->
                    <!-- Table body -->
                </table>
                <!-- Table  -->
                <div v-show="data.data.length <= 0">
                    No data found!
                </div>
            </div>
            <div class="card-footer">
                <div class="paginator pagination example" v-show="data.to > 1">
                    <ul class="pagination pg-blue">
                        <li class="page-item" v-bind:class="{ 'disabled': data.current_page == 1 }">
                            <a class="page-link" aria-label="Previous" v-on:click="changePage(data.current_page - 1)">
                                <span aria-hidden="true">&laquo;</span>
                                <span class="sr-only">Previous</span>
                            </a>
                        </li>
                        <li class="page-item" v-bind:class="{ 'active': data.current_page == 1 }">
                            <a class="page-link" v-on:click="changePage(1)">
                                1
                            </a>
                        </li>
                        <li class="page-item" v-if="(data.current_page - 1) != 1 && (data.current_page) != 1">
                            <a class="page-link" v-on:click="changePage(data.current_page - 1)">
                                {{ data.current_page - 1 }}
                            </a>
                        </li>
                        <li class="active page-item" v-if="data.current_page != 1">
                            <a class="page-link">
                                {{ data.current_page }}
                            </a>
                        </li>
                        <li class="page-item" v-if="(data.current_page + 1) != data.last_page && (data.current_page) != data.last_page">
                            <a v-on:click="changePage(data.current_page + 1)" class="page-link">
                                {{ data.current_page + 1 }}
                            </a>
                        </li>
                        <li class="page-item" v-if="data.current_page != data.last_page">
                            <a class="page-link" v-on:click="changePage(data.last_page)">
                                {{ data.last_page }}
                            </a>
                        </li>
                        <li class="page-item" v-bind:class="{ 'disabled': data.current_page == data.last_page }">
                            <a class="page-link" aria-label="Next" v-on:click="changePage(data.current_page + 1)">
                                <span aria-hidden="true">&raquo;</span>
                                <span class="sr-only">Next</span>
                            </a>
                        </li>                        
                    </ul>
                </div>
                <div class="paginatorInfo" v-if="data.total > 1">
                    Showing {{data.from}} to {{data.to}} of {{data.total}}
                </div>
            </div>
        </div>
        <!--/.Card-->
    </div>
    <!--Grid column-->
</template>

<script>
    import Print from '../mixins/print'
    import PDF from '../mixins/pdf'
    import Error from '../mixins/errors'
    import CSV from '../mixins/csv'

    export default {
        props: 
            ['dataUrl', 'columnClass', 'tableTitle', 'hasRange', 
            'generalQuery', 'redirectName', 'redirectId', 'editName', 
            'editId', 'filters', 'hiddenColumns', 'deleteUrl', 'deleteId',
            'csvUrl'
            ],
        mixins: [ Print, PDF, CSV, Error ],
        data: function () {
            return {
                data: {
                    current_page: 1,
                    to: 1,
                    data:{}
                },
                loading: false,
                pageSelection: 1,
                range: {label: 'Last month', value: '30'},
                rangeOptions: [      
                    {label: 'Last week', value: '7'},
                    {label: 'Last 2 weeks', value: '14'},
                    {label: 'Last month', value: '30'},
                    {label: 'Last 2 months', value: '60'},
                    {label: 'Last 6 months', value: '180'},
                ],
                //queryString: localStorage.getItem(this.tableTitle+'query') || '',
                queryString: '',
                selectedFilters: [],
                orderBy: '',
                orderDirection: '',
                limit: {label: 'Show 10', value: '10'},
                limitOptions: [      
                    {label: 'Show 10', value: '10'},
                    {label: 'Show 20', value: '20'},
                    {label: 'Show 50', value: '50'},
                    {label: 'Show 100', value: '100'},
                    {label: 'Show 200', value: '200'},
                    {label: 'Show All', value: '900000'},
                ],
            }
         },
        mounted() {
            this.getData();
        },
        computed: {
            currentPageParam: function (){
                return '?page='+this.data.current_page;
            },
            currentRangeParam: function (){
                return this.hasRange ? '&range='+this.range.value : '';
            },
            currentQueryString: function (){
                return this.queryString != '' ? '&q='+this.queryString : '';
            },
            currentOrderParam: function(){
                return this.orderBy != '' ? '&orderBy='+this.orderBy+'&orderDirection='+this.orderDirection : '';
            },
            currentLimitParam: function(){
                return this.limit.value != '' ? '&limit='+this.limit.value : '';
            }
        },
        watch: {
            queryString: _.debounce(function() {
                //localStorage.setItem(this.tableTitle+'query', this.queryString);
                this.data.current_page = 1;
                this.getData();
            },500),
            range: function(){
                this.getData();
            },
            generalQuery: function() {
                this.queryString = this.generalQuery;
            },
            filter: function(){
                console.log(this.filter);
            },
            limit: function(){
                this.getData();
            },
        },
        methods: {
            getData: function(){
                this.loading = true;

                axios.get(
                    this.dataUrl+this.currentPageParam+
                    this.currentQueryString+this.currentRangeParam+
                    this.currentOrderParam+this.currentLimitParam
                )
                .then((response) => {
                    this.data = response.data.data;
                    this.loading = false;
                })
                .catch((error) => {
                    this.reportError(error);
                })                
            },
            changePage: function(page){
                if(page === this.data.current_page) return;
                this.data.current_page = page;
                this.getData();
            },
            setOrder: function(key){
                this.orderDirection = this.orderDirection == '' ? 'DESC' : this.orderDirection == 'DESC' ? 'ASC' : '';
                this.orderBy = this.orderDirection == '' ? '' : key;
                this.getData();
            },
            redirect: function(id){
                if(this.redirectName && this.redirectId){
                    this.$router.push({name: this.redirectName, params: {id: id}});
                }
            },
            edit: function(id){
                if(this.editName && this.editId){
                    this.$router.push({name: this.editName, params: {id: id}});
                }
            },
            deleteItem: function(id){
                this.$swal({
                    title: 'Are you sure you want to delete this item?',
                    type: 'warning',
                    showConfirmButton: true,
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes!'                            
                }).then((result) => {
                    if (result.value) {
                        axios.delete(this.deleteUrl+'/'+id)
                        .then((response) => {
                            this.$emit('tableupdate');
                            this.postSuccess('Item successfully deleted!');
                            this.getData();
                        })
                        .catch((error) => {
                            console.log(error);
                            this.postError(error);
                        })   
                                            
                    }
                });
            }
        }
    }
</script>
