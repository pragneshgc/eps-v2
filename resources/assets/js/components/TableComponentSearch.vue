<template>
    <!--Grid column-->
    <div class=" natcol-table" :class="[columnClass]">
        <transition name="fade">
            <div class="loader" v-show="loading">Loading...</div>
        </transition>

        <!--Card-->
        <div class="card">
            <div class="">
                <div class="row search-boxes">
                    <div v-for="(filter, index) in filters" :key="index" class="filter-inputs">
                        <!-- {{ filter.title }} -->
                        <input v-model="selectedFilters[filter.value]" v-if="filter.type == 'text'"
                            class="form-control tBoxSize02" :placeholder="filter.title" />
                        <datepicker v-else-if="filter.type == 'date'" :placeholder="filter.title" :name="filter.value"
                            v-model="selectedFilters[filter.value]" maxlength="30">
                        </datepicker>
                        <select class="table-dropdown" v-else-if="filter.type == 'select'" :name="filter.value"
                            v-model="selectedFilters[filter.value]">
                            <option v-for="(option, index) in filter.options" :key="index" :value="option.value">
                                {{ option.title }}
                            </option>
                        </select>

                        <treeselect v-else-if="filter.type == 'select-extended'" class="vue-treeselect-reports"
                            :open-on-click="true" :open-on-focus="true" :open-on-hover="true"
                            :multiple="filter.multiple ? true : false" :clearable="filter.clearable ? true : false"
                            :searchable="true"
                            :disable-branch-nodes="filter.disableBranchNodes ? filter.disableBranchNodes : false"
                            :placeholder="filter.placeholder" :show-count="true" :default-expand-level="1"
                            :append-to-body="false" :options="filter.options" :instanceId="filter.value"
                            @open="selectOpen" v-model="selectedFilters[filter.value]">
                            <div slot="value-label" slot-scope="{ node }">
                                {{ node.raw.customLabel ? node.raw.customLabel : node.raw.label }}
                            </div>
                            <label slot="option-label"
                                slot-scope="{ node, shouldShowCount, count, labelClassName, countClassName }"
                                :class="labelClassName">
                                {{ node.label }}
                                <span v-if="shouldShowCount" :class="countClassName">({{ count }})</span>
                            </label>
                        </treeselect>

                        <treeselect v-else-if="filter.type == 'select-async'" class="vue-treeselect-reports"
                            :multiple="true" :load-options="filter.loadOptions" :options="filter.options"
                            :auto-load-root-options="true" v-model="selectedFilters[filter.value]">
                            <template #value-label="{ node }">
                                <div>{{ node.raw.customLabel ? node.raw.customLabel : node.raw.label }}</div>
                            </template>
                        </treeselect>

                        <!-- <treeselect v-else-if="filter.type == 'select-async'" class="vue-treeselect-reports"
                            :open-on-click="true" :clearable="filter.clearable ? true : false" :open-on-focus="true"
                            :open-on-hover="true" :searchable="true"
                            :disable-branch-nodes="filter.disableBranchNodes ? filter.disableBranchNodes : false"
                            :cache-options="true" :placeholder="filter.placeholder" :show-count="true"
                            :default-expand-level="1" :async="true" :load-options="filter.loadOptions"
                            :options="filter.options" :default-options="false"
                            :multiple="filter.multiple ? true : false" :append-to-body="false"
                            v-model="selectedFilters[filter.value]">
                            <template #value-label="{ node }" slot-scope="{ node }">
                                <div>{{ node.raw.customLabel ? node.raw.customLabel : node.raw.label }}</div>
                            </template>

                        </treeselect> -->

                        <input v-model="selectedFilters[filter.value]" v-else class="form-control tBoxSize02"
                            :placeholder="filter.title" />
                    </div>
                </div>
                <div class="row filters-row">
                    <div>
                        <input name="strict" type="checkbox" :checked="strict">
                        <label for="strict" @click="strict = !strict">Exact match</label>
                    </div>
                    <button :disabled="loading" class="btn btnSize02 tertiaryBtn" @click="getData()">Search</button>
                </div>
                <div class="row filters-row">
                    <div class="filter-inputs">
                        <!-- <input v-model="queryString" type="text" class="form-control tBoxSize02" id="queryString" placeholder="Search..."> -->
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
                        <a class="btn btn-primary waves-effect" v-on:click="exportPDF($el)"><i class="fa fa-file-pdf-o"
                                aria-hidden="true"></i></a>
                        <a v-if="csvUrlSearch" class="btn btn-primary waves-effect" :href="csvUrlSearch"><i
                                class="fa fa-file" aria-hidden="true"></i></a>
                        <a v-else class="btn btn-primary waves-effect" v-on:click="exportCSV(data.data, tableTitle)"><i
                                class="fa fa-file" aria-hidden="true"></i></a>
                    </div>
                </div>
                <slot>
                </slot>
            </div>
            <!--Card content-->
            <div class="card-body">
                <!-- Table  -->
                <table style="table-layout: auto;" v-show="data.data.length >= 1" class="table table-hover">
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
                            <th class="clickable" v-for="(value, key) in data.data[0]"
                                v-if="typeof hiddenColumns != 'undefined' ? !hiddenColumns.includes(key) : true"
                                v-on:click="setOrder(key)">
                                {{ translate(key) }}

                                <i v-if="key == orderBy && orderDirection == 'DESC'" class="fa fa-caret-down"></i>
                                <i v-if="key == orderBy && orderDirection == 'ASC'" class="fa fa-caret-up"></i>
                                <i v-if="key != orderBy" class="fa fa-sort"></i>
                            </th>
                            <th style="width: 50px;"></th>
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
                                <input :name="item[Object.keys(item)[0]]" type="checkbox"
                                    :checked="checked.includes(item[Object.keys(item)[0]])">
                                <label :for="item[Object.keys(item)[0]]" @click="check(item)"></label>
                                <!-- <span class="checkmark"></span>  -->
                                <!-- </label> -->
                            </td>
                            <td v-for="(value, key) in item"
                                v-if="typeof hiddenColumns != 'undefined' ? !hiddenColumns.includes(key) : true">
                                <span v-if="typeof value == 'string'" v-html="value"></span>
                                <ul v-else-if="typeof value == 'object'">
                                    <li v-for="obj in value" v-html="obj"></li>
                                </ul>
                                <span v-else v-html="value"></span>
                            </td>
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
                    No data found!
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

<style>
@import "@zanmato/vue3-treeselect/dist/vue3-treeselect.min.css";
</style>

<script>
import TableComponent from './TableComponent.vue';
import Datepicker from 'vuejs3-datepicker';
import Treeselect from "@zanmato/vue3-treeselect";
export default {
    extends: TableComponent,
    components: {
        Datepicker, Treeselect
    },
    data() {
        return {
            strict: true,
        }
    },
    computed: {
        csvUrlSearch() {
            if (this.tableTitle == 'Register') {
                return '/orders/csv/register' + this.currentPageParam +
                    this.currentQueryString + this.currentRangeParam +
                    this.currentOrderParam + this.currentLimitParam +
                    this.filterParams();
            } else {
                return '/reports/csv' + this.currentPageParam +
                    this.currentQueryString + this.currentRangeParam +
                    this.currentOrderParam + this.currentLimitParam +
                    this.filterParams();
            }
        },
        noSelectedFilters() {
            if (Object.keys(this.selectedFilters).length == 0) {
                return true;
            } else {
                let empty = true;
                let keys = Object.keys(this.selectedFilters);

                for (let key in Object.keys(this.selectedFilters)) {
                    if (Array.isArray(this.selectedFilters[keys[key]]) && this.selectedFilters[keys[key]].length > 0) {
                        empty = false;
                    } else if (this.selectedFilters[keys[key]] && !Array.isArray(this.selectedFilters[keys[key]])) {
                        empty = false;
                    }
                }

                return empty;
            }
        }
    },
    mounted() {
        this.filters.forEach(filter => {
            if (filter.type == 'select') {
                if (filter.selected) {
                    this.selectedFilters[filter.value] = filter.selected;
                } else {
                    this.selectedFilters[filter.value] = '';
                }
            }
        });
    },
    methods: {
        getData: function () {
            this.loading = true;

            axios.get(
                this.dataUrl + this.currentPageParam +
                this.currentQueryString + this.currentRangeParam +
                this.currentOrderParam + this.currentLimitParam +
                this.filterParams()
            )
                .then((response) => {
                    this.data = response.data.data;
                    this.loading = false;

                    let visible = this.data.data.map(function (item) {
                        return item[Object.keys(item)[0]];
                    });
                })
                .catch((error) => {
                    this.reportError(error);
                })
        },
        selectOpen(instance) {
            //this is a specific fix for statuses since we need them to be wider than usual (when opening substatus selectors)
            if (instance == 'status-extended') {
                let element = document.querySelector('[data-instance-id="status-extended"]').firstChild
                element.classList.add('vue-treeselect__menu-container-body');
            }
        },
        filterParams() {
            let param = JSON.stringify(this.selectedFilters);

            return `&f=${param}&strict=${this.strict}`;
        },
    },
}
</script>
