export default {
    data() {
        return {
            orderStatuses: {
                // 1: 'NEW',
                // 2: 'APPROVED',
                // 3: 'REJECTED',
                // 4: 'QUERIED',
                // 5: 'POSTPONED',
                6: 'CANCELLED',
                7: 'Awaiting Shipment',
                8: 'SHIPPED',
                // 9: 'SAFETYCHECK',
                // 10: 'ONHOLD',
                // 11: 'CALL',
                // 12: 'QUERIEDDISPENSED',
                // 13: 'QUERIEDNOTDISPENSED',
                // 14: 'QUERIEDNOREPLY',
                // 15: 'QUERIEDARCHIVED'                    
            },
            orderStatusesSelect: [
                {
                    title: 'Select Prescription Status',
                    value: ''
                }, 
                // {
                //     title: 'SAFETYCHECK',
                //     value: '9'
                // },
                // {
                //     title: 'NEW',
                //     value: '1'
                // },
                // {
                //     title: 'APPROVED',
                //     value: '2'
                // },
                {
                    title: 'AWAITINGSHIPPED',
                    value: '7'
                },
                {
                    title: 'SHIPPED',
                    value: '8'
                },
                // {
                //     title: 'ONHOLD',
                //     value: '10'
                // },
                // {
                //     title: 'QUERIED',
                //     value: '4'
                // },
                // {
                //     title: 'QUERIEDDISPENSED',
                //     value: '12'
                // },
                // {
                //     title: 'QUERIEDNOTDISPENSED',
                //     value: '13'
                // },
                // {
                //     title: 'QUERIEDNOREPLY',
                //     value: '14'
                // },
                // {
                //     title: 'QUERIEDARCHIVED',
                //     value: '15'
                // },
                // {
                //     title: 'REJECTED',
                //     value: '3'
                // },
                {
                    title: 'CANCELLED',
                    value: '6'
                },
                // {
                //     title: 'POSTPONED',
                //     value: '5'
                // },
                // {
                //     title: 'CALL',
                //     value: '11'
                // },
                // {
                //     title: 'RETURN',
                //     value: '16'
                // },
            ]
        }
    }
}