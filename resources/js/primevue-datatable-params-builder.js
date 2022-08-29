import {FilterMatchMode} from "primevue/api";

// builds a query string which is processed by spatie laravel query builder
export function buildQueryUrl(lazyParams, baseUrl) {
    let queryString = baseUrl + '?';

    queryString += buildPageBlock(lazyParams['page']);

    queryString += buildFilterBlock(lazyParams['filters']);

    queryString += buildSortBlock(lazyParams);

    return queryString.slice(0, -1);
}

function buildPageBlock(primePage) {
    if(primePage === undefined || primePage === null || primePage === 0) return '';

    const page = Number(primePage) + 1;

    return 'page=' + page + '&';
}

function buildFilterBlock(filters) {
    if(filters === undefined || filters === null) return '';

    let queryString = '';

    for (const [key, value] of Object.entries(filters)) {
        if(value.constraints !== undefined) {
            let str = '';
            const filterConstraint = value.constraints[0];

            if(filterConstraint.matchMode === FilterMatchMode.CONTAINS && filterConstraint.value !== null) {
                str = filterConstraint.value;
                str = str.replace(/ +(?= )/g,'').trim().replace(/ /g,",");
            } else if(filterConstraint.matchMode === FilterMatchMode.EQUALS && filterConstraint.value !== null) {
                str = filterConstraint.value;
                str = String(str).trim();
            } else continue;

            queryString += 'filter['+key+']=' + str + '&';

        } else if(value.matchMode !== undefined && value.value !== null) {
            queryString += 'filter['+key+']=';

            value.value.forEach((x, index)=> {
                queryString += x.name;

                if(index < value.value.length-1) {
                    queryString += ',';
                }
            })

            queryString += '&';
        }
    }

    return queryString;
}

function buildSortBlock(lazyParams) {
    let queryString = ''

    if (lazyParams["sortField"] !== null) {
        let sortField = lazyParams["sortField"];

        if (lazyParams["sortOrder"] === -1) {
            sortField = "-" + sortField;
        }

        queryString += 'sort=' + sortField + '&';
    }

    return queryString;
}
