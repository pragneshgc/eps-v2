<template>
    <!--Grid column-->
    <div class=" natcol-table" :class="[columnClass]">
        <!-- <transition name="fade"> -->
        <div class="loader" v-show="loading">Loading...</div>
        <!-- </transition> -->

        <!--Card-->
        <div class="card">
            <div class="card-header">
                <div class="row filters-row">
                    <div class="filter-inputs">
                        <input v-model="queryString" type="text" class="form-control tBoxSize02" id="queryString"
                            placeholder="Search...">
                        <select v-model="limit" class="table-dropdown">
                            <option value="10">Show 10</option>
                            <option value="20">Show 20</option>
                            <option value="50">Show 50</option>
                            <option value="100">Show 100</option>
                            <option value="200">Show 200</option>
                            <option value="9999999999">Show All</option>
                        </select>
                    </div>
                    <div class="dropdown float-right">
                        <a class="btn btn-primary waves-effect" v-on:click="printChart($el)"><i class="fa fa-print"
                                aria-hidden="true"></i></a>
                        <a class="btn btn-primary waves-effect" v-on:click="exportPDF(this.$refs.cardbody)"><i
                                class="fa fa-file-pdf-o" aria-hidden="true"></i></a>
                        <a v-if="csvUrl" class="btn btn-primary waves-effect" :href="csvUrl"><i class="fa fa-file"
                                aria-hidden="true"></i></a>
                        <a v-else class="btn btn-primary waves-effect" v-on:click="exportCSV(data.data, tableTitle)"><i
                                class="fa fa-file" aria-hidden="true"></i></a>
                    </div>
                </div>
                <slot>
                </slot>
            </div>
            <!--Card content-->
            <div class="card-body" ref="cardbody">
                <!-- Table  -->
                <table v-show="data.data.length >= 1" class="table table-hover">
                    <!-- Table head -->
                    <thead class="primary-color text-white">
                        <tr>
                            <th v-if="checkboxVisible" style="
                                width: 40px;
                                font-weight: 400;
                                padding: 16px 8px;
                                vertical-align: top;
                            ">
                            </th>
                            <template v-for="(value, key) in data.data[0]">
                                <template
                                    v-if="typeof hiddenColumns != 'undefined' ? !hiddenColumns.includes(key) : true">
                                    <th class="clickable" v-on:click="setOrder(key)">
                                        {{ translate(key) }}

                                        <i v-if="key == orderBy && orderDirection == 'DESC'"
                                            class="fa fa-caret-down"></i>
                                        <i v-if="key == orderBy && orderDirection == 'ASC'" class="fa fa-caret-up"></i>
                                        <i v-if="key != orderBy" class="fa fa-sort"></i>
                                    </th>
                                </template>
                            </template>
                            <th style="width: 80px;"></th>
                        </tr>
                    </thead>
                    <!-- Table head -->

                    <!-- Table body -->
                    <!--<transition-group tag="tbody" name="table-row">-->
                    <tbody>
                        <!--@dblclick="redirect(item[redirectId])"   -->
                        <tr class="clickable" @dblclick="redirect(item[redirectId])" v-for="item in data.data"
                            :key="item[Object.keys(item)[0]]">
                            <td v-if="checkboxVisible">
                                <!-- <label class="checkboxElement"> -->
                                <input :name="item[Object.keys(item)[0]]" type="checkbox" :disabled="item.disabled == 1"
                                    :checked="checked.includes(item[Object.keys(item)[0]]) || item.disabled == 1">
                                <label :for="item[Object.keys(item)[0]]" @click="check(item)"></label>
                                <!-- <span class="checkmark"></span>  -->
                                <!-- </label> -->
                            </td>
                            <template v-for="(value, key) in item">
                                <template
                                    v-if="typeof hiddenColumns != 'undefined' ? !hiddenColumns.includes(key) : true">
                                    <td>
                                        <ul v-if="typeof value == 'object'">
                                            <li v-for="obj in value">{{ obj }}</li>
                                        </ul>
                                        <span v-else v-html="value"></span>
                                    </td>
                                </template>
                                <template>
                                    <td>
                                        <span v-html="value"></span>
                                    </td>
                                </template>
                            </template>

                            <td>
                                <a class="btn btn-primary waves-effect table-icon" @click="redirect(item[redirectId])">
                                    <i class="fa fa-search-plus" aria-hidden="true"></i>
                                </a>
                                <a v-if="deleteId" class="btn btn-primary waves-effect table-icon"
                                    v-on:click="deleteItem(item[deleteId])" v-bind:class="{ 'clickable': deleteId }">
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
                    No prescriptions found!
                </div>
            </div>
            <div class="card-footer">
                <div class="paginator pagination example" v-show="data.to > 1">
                    <ul class="pagination pg-blue">
                        <li class="page-item" v-bind:class="{ 'disabled': data.current_page == 1 }"
                            v-on:click="changePage(data.current_page - 1)">
                            <a class="page-link" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                                <span class="sr-only">Previous</span>
                            </a>
                        </li>
                        <li class="page-item" v-bind:class="{ 'active': data.current_page == 1 }"
                            v-on:click="changePage(1)">
                            1
                        </li>
                        <li class="page-item" v-if="(data.current_page - 1) != 1 && (data.current_page) != 1"
                            v-on:click="changePage(data.current_page - 1)">
                            {{ data.current_page - 1 }}
                        </li>
                        <li class="active page-item" v-if="data.current_page != 1">
                            {{ data.current_page }}
                        </li>
                        <li class="page-item"
                            v-if="(data.current_page + 1) != data.last_page && (data.current_page) != data.last_page"
                            v-on:click="changePage(data.current_page + 1)">
                            {{ data.current_page + 1 }}
                        </li>
                        <li class="page-item" v-if="data.current_page != data.last_page"
                            v-on:click="changePage(data.last_page)">
                            {{ data.last_page }}
                        </li>
                        <li class="page-item" v-bind:class="{ 'disabled': data.current_page == data.last_page }"
                            v-on:click="changePage(data.current_page + 1)">
                            <a class="page-link" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                                <span class="sr-only">Next</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="paginatorInfo" v-if="data.total > 1">
                    Showing {{ data.from }} to {{ data.to }} of {{ data.total }}
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
        [
            'dataUrl', 'columnClass', 'tableTitle', 'hasRange',
            'generalQuery', 'redirectName',
            'redirectId', 'filters', 'hiddenColumns',
            'csvUrl', 'columnMap', 'deleteUrl', 'deleteId',
            'checkboxVisible'
        ],
    mixins: [Print, PDF, CSV, Error],
    data: function () {
        return {
            data: {
                current_page: this.$route.params.p || 1,
                to: 1,
                data: {}
            },
            loading: false,
            pageSelection: 1,
            range: { label: 'Last month', value: '30' },
            rangeOptions: [
                { label: 'Last week', value: '7' },
                { label: 'Last 2 weeks', value: '14' },
                { label: 'Last month', value: '30' },
                { label: 'Last 2 months', value: '60' },
                { label: 'Last 6 months', value: '180' },
            ],
            queryString: this.$route.params.q || '',
            selectedFilters: {},
            orderBy: '',
            filter: {},
            orderDirection: '',
            limit: '200',
        }
    },
    computed: {
        currentPageParam: function () {
            return '?page=' + this.data.current_page;
        },
        currentRangeParam: function () {
            return this.hasRange ? '&range=' + this.range.value : '';
        },
        currentQueryString: function () {
            return this.queryString != '' ? '&q=' + this.queryString : '';
        },
        currentOrderParam: function () {
            return this.orderBy != '' ? '&orderBy=' + this.orderBy + '&orderDirection=' + this.orderDirection : '';
        },
        currentLimitParam: function () {
            return this.limit.value != '' ? '&limit=' + this.limit : '';
        },
        currentFilterParam: function () {
            return this.filters != '' ? '&f=' + this.filters : '';
        },
        checked() {
            return this.$store.state.checked;
        },
    },
    watch: {
        queryString: _.debounce(function () {
            //localStorage.setItem(this.tableTitle+'query', this.queryString);
            this.data.current_page = 1;
            this.getData();
        }, 500),
        range: {
            handler: 'getData',
            immediate: true
        },
        limit: 'getData', // we call the method named getData
        generalQuery: function () {
            this.queryString = this.generalQuery;
        },
        filters: function () {
            this.data.current_page = 1;
            this.getData();
        },
        '$route.query': function () {
            if (typeof this.$route.params.q != 'undefined') {
                this.queryString = this.$route.params.q;
            } else {
                this.queryString = '';
            }
            if (typeof this.$route.params.p != 'undefined') {
                this.data.current_page = this.$route.params.p;
                this.getData(); // this was called twice once from here once from the button change
            }
        }
    },
    mounted() {
        this.emitter.on('table.check.all', (e) => {
            this.checkAll();
        });
        this.emitter.on('table.refresh', (e) => {
            this.getData();
        });
        this.emitter.on('table.uncheck.all', (e) => {
            this.uncheckAll();
        });
    },
    destroyed() {
        this.emitter.off('table.check.all');
        this.emitter.off('table.uncheck.all');
        this.emitter.off('table.refresh');
    },
    methods: {
        getData: function () {
            this.loading = true;

            axios.get(
                this.dataUrl + this.currentPageParam +
                this.currentQueryString + this.currentRangeParam +
                this.currentOrderParam + this.currentLimitParam +
                this.currentFilterParam
            )
                .then((response) => {
                    this.data = response.data.data;
                    this.loading = false;

                    let visible = this.data.data.map(function (item) {
                        return item[Object.keys(item)[0]];
                    });

                    // this.$store.commit('replaceVisible', visible);
                })
                .catch((error) => {
                    console.log(error);
                    this.reportError(error);
                })
        },
        changePage: function (page) {
            if (page === this.data.current_page) return;
            this.data.current_page = page;
            this.getData();
        },
        setOrder: function (key) {
            this.orderDirection = this.orderDirection == '' ? 'DESC' : this.orderDirection == 'DESC' ? 'ASC' : '';
            this.orderBy = this.orderDirection == '' ? '' : key;
            this.getData();
        },
        redirect: function (id) {
            if (this.redirectName && this.redirectId) {
                this.$router.push({ name: this.redirectName, params: { id: id } });
            }
        },
        check(item) {
            if (item.disabled != 1) {
                this.$store.commit('toggleChecked', item[Object.keys(item)[0]]);
            }
        },
        checkAll() {
            this.data.data.forEach(i => {
                if (i.disabled != 1) {
                    this.$store.commit('addChecked', i[Object.keys(i)[0]]);
                }
            });
        },
        uncheckAll() {
            this.data.data.forEach(i => {
                this.$store.commit('removeChecked', i[Object.keys(i)[0]]);
            });
        },
        translate(value) {
            if (typeof this.columnMap == 'undefined') {
                return value;
            } else {
                if (typeof this.columnMap[value] != 'undefined') {
                    return this.columnMap[value];
                }
            }

            return value;
        },
        deleteItem: function (id) {
            this.$swal({
                title: 'Are you sure you want to delete this item?',
                icon: 'warning',
                showConfirmButton: true,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes!'
            }).then((result) => {
                if (result.value) {
                    axios.delete(this.deleteUrl + '/' + id)
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
        },
    }
}
</script>
