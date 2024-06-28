<script setup>
import Chart from 'chart.js/auto';
import moment from 'moment';
import 'chart.js';
import 'chartjs-adapter-moment';
import { onMounted, ref, computed } from 'vue';
import axios from 'axios'
import { useUserStore } from '@/stores/user.js'

const transactions = ref([])
const categories = ref([])
const paymentTypes = ref([])
const vcards = ref([])
const transactionsMonth = ref([])
const transactionsType = ref([])
const userStore = useUserStore()

const flag = userStore.user.user_type == 'A' ? false : true
const numberOfTransactions = ref(null)
const balance = ref(null)
const numberOfCategories = ref(null)
const activeVcards = ref(null)
const numberofAllTransactions = ref(null)
const vcardsBalances = ref(null)
const distributionOfUsers = ref(null)

const loadTransactions = async () => {
    if (!flag)
        return
    try {
        const response = await axios.get(`vcards/${userStore.user.id}/transactions?requested=nreq`)
        transactions.value = response.data.data
        numberOfTransactions.value = response.data.meta.total
        balance.value = numberOfTransactions.value ? transactions.value[0]['new_balance'] : 0
    } catch (error) {
        console.log(error)
    }
};

const loadCategories = async () => {
    if (!flag)
        return
    try {
        const response = await axios.get(`vcards/${userStore.user.id}/transactionsCategories`)
        categories.value = response.data
        numberOfCategories.value = categories.value.length
    } catch (error) {
        console.log(error)
    }
};

const loadPaymentTypes = async () => {
    try {
        const response = flag ? await axios.get(`vcards/${userStore.user.id}/transactionsPaymentTypes`) : await axios.get(`paymentTypesOftransactions`)
        paymentTypes.value = response.data
    } catch (error) {
        console.log(error)
    }
};

const loadVcardsActive = async () => {
    if (flag)
        return
    try {
        const response = await axios.get(`vcardsActive`)
        vcards.value = response.data
        activeVcards.value = vcards.value.length

        const balances = vcards.value.map(item => parseFloat(item.balance) || 0);
        const totalBalance = balances.reduce((acc, balance) => acc + balance, 0);
        const formattedTotalBalance = totalBalance.toFixed(2);
        vcardsBalances.value = formattedTotalBalance;

    } catch (error) {
        console.log(error)
    }
};

const loadTransactionsNotDeleted = async () => {
    if (flag)
        return
    try {
        const response = await axios.get(`transactionsValid`)
        numberofAllTransactions.value = response.data
    } catch (error) {
        console.log(error)
    }
};

const loadDistributionOfUsers = async () => {
    if (flag)
        return
    try{
        const response = await axios.get('distributionOfUsers')
        distributionOfUsers.value = response.data
    }catch(error){
        console.log(error)
    }
}

const loadTransactionsPerMonth = async () => {
    if (flag)
        return
    try {
        const response = await axios.get(`transactionsPerMonth`)
        transactionsMonth.value = response.data
    } catch (error) {
        console.log(error)
    }
}

const loadTransactionsPerType = async () => {
    if (flag)
        return
    try {
        const response = await axios.get(`transactionsPerType`)
        transactionsType.value = response.data
    } catch (error) {
        console.log(error)
    }
}

const createChartLineTransactionsType = () => {
    const ctx = document.getElementById('myChartPolarAreaTransactionType');
    const types = transactionsType.value.map((transaction) => (transaction.type == 'C' ? 'Credit' : 'Debit'));
    const types_count = transactionsType.value.map((transaction) => (transaction.count));

    new Chart(ctx, {
        type: 'polarArea',
        data: {
            labels: types,
            datasets: [
                {
                    label: 'Transaction Per Type',
                    data: types_count,
                    borderWidth: 1,
                    fill: false,
                },
            ],
        },
        options: {
            plugins: {
                title: {
                    display: true,
                    text: 'TRANSACTIONS PER TYPE',
                    font: {
                        size: 20,
                    },
                    padding: {
                        bottom: 20,
                    },
                },
            },
            layout: {
                padding: {
                    left: 10,
                    right: 10,
                    top: 10,
                    bottom: 10,
                },
            },
        },
    })
};

const createChartLineTransactions = () => {
    const ctx = document.getElementById('myChartLineTransactionsMonth');
    const monthNames = [
        "Jan", "Feb", "Mar", "Apr", "May", "June",
        "July", "Aug", "Sept", "Oct", "Nov", "Dec"];

    const months = transactionsMonth.value.map((transaction) => transaction.month);
    const years = transactionsMonth.value.map((transaction) => transaction.year);
    const transaction_count = transactionsMonth.value.map((transaction) => transaction.transaction_count);

    months.reverse();
    years.reverse();

    const formattedDates = months.map((month, index) => {
        const monthName = monthNames[month - 1];
        return `${monthName}-${years[index]}`;
    });

    transaction_count.reverse();

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: formattedDates,
            datasets: [
                {
                    label: 'Transactions',
                    data: transaction_count,
                    borderWidth: 1,
                    fill: false,
                },
            ],
        },
        options: {
            plugins: {
                title: {
                    display: true,
                    text: 'TRANSACTIONS PER MONTH',
                    font: {
                        size: 20,
                    },
                    padding: {
                        bottom: 20,
                    },
                },
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Month',
                    },
                },
                y: {
                    title: {
                        display: true,
                        text: 'Transactions',
                    },
                    ticks: {
                        beginAtZero: true,
                    },
                },
            },
            layout: {
                padding: {
                    left: 10,
                    right: 10,
                    top: 10,
                    bottom: 10,
                },
            },
        },
    })
};

const createChartLine = () => {
    const ctx = document.getElementById('myChartLineTransactions')
    const formattedDates = transactions.value.map((transaction) => moment(transaction.datetime))
    const newBalances = transactions.value.map((transaction) => transaction.new_balance)
    const minBalance = Math.floor(Math.min(...newBalances) / 10) * 10;
    const maxBalance = Math.ceil(Math.max(...newBalances) / 10) * 10;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: formattedDates,
            datasets: [
                {
                    label: 'Balance',
                    data: newBalances,
                    borderWidth: 1,
                    fill: false,
                },
            ],
        },
        options: {
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'day',
                        tooltipFormat: 'YYYY-MM-DD HH:mm:ss',
                    },
                    title: {
                        display: true,
                        text: 'Date',
                    },
                },
                y: {
                    min: minBalance + 0,
                    max: maxBalance + 50,
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Balance',
                    },
                    ticks: {
                        callback: function (value, index, values) {
                            return value + '€'
                        },
                    },
                },
            },
            plugins: {
                title:{
                    display: true,
                    text: 'LAST TRANSACTIONS',
                    font: {
                        size: 20,
                    },
                    padding: {
                        bottom: 20,
                    },
                },
                legend: {
                    display: true,
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            var label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed.y.toFixed(2) + ' €';
                            return label;
                        }
                    }
                }
            },
            layout: {
                padding: {
                    left: 10,
                    right: 10,
                    top: 10,
                    bottom: 10,
                },
            },
        },
    })
}

const createChartPie = () => {
    const ctx = document.getElementById('myChartPie')

    const paymentsMethod = paymentTypes.value.map((transaction) => transaction.payment_type)

    const newCounts = paymentTypes.value.map((transaction) => transaction.count)

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: paymentsMethod,
            datasets: [
                {
                    label: 'Number of Payments',
                    data: newCounts,
                    borderWidth: 1,
                    fill: false,
                },
            ],
        },
        options: {
            //responsive: false,
            //maintainAspectRatio: false,
            // layout: {
            //     padding: {
            //         left: 50
            //     }
            // }
            plugins: {
                title:{
                    display: true,
                    text: 'TRANSACTIONS PER PAYMENT TYPE',
                    font: {
                        size: 20,
                    },
                    padding: {
                        bottom: 20,
                    },
                },    
            },
        },
    })
};

const createChartVertical = () => {
    const ctx = document.getElementById('myChartBarVerticalUsers')
    const total = distributionOfUsers.value[0].count + distributionOfUsers.value[1].count
    const distribution = distributionOfUsers.value.map((u) =>( u.count * 100 / total).toFixed(0))
    const user_type = distributionOfUsers.value.map((u) => u.user_type)

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: user_type,
            datasets: [
                {
                    label: 'Percentage of User',
                    data: distribution,
                    borderWidth: 1,
                    borderColor: 'rgb(153, 102, 255)',
                    backgroundColor: 'rgb(153, 102, 255)',
                    fill: false,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'DISTRIBUTION OF USERS',
                    font: {
                        size: 20,
                    },
                    padding: {
                        bottom: 20,
                    },
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            var label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed.y + '%';
                            return label
                        },
                        title: function (tooltipItems) {
                            return tooltipItems[0].label === 'A' ? 'Administrator' : 'Vcard Owner';
                        },
                    }
                }
            },
            scales: {
                y: {
                    min: 0,
                    max: 100,
                    ticks: {
                        callback: function (value) {
                            return value + '%';
                        },
                    },
                    grid: {
                        display: false,
                    },
                },
                x: {
                    ticks: {
                        callback: function (value) {
                            return value ? 'Administrator' : 'Vcard Owner';
                        },
                    },
                },
            },
        },
    });

}

const createChartBarHorizontal = () => {
    const ctx = document.getElementById('myChartBarHorizontalTransactionsCategories');
    const categoriesName = categories.value.map((categorie) => categorie.name);
    const categoriesNumbers = categories.value.map((categorie) => categorie.count);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: categoriesName,
            datasets: [
                {
                    label: 'Number of Transactions',
                    data: categoriesNumbers,
                    borderWidth: 1,
                    borderColor: 'rgba(255, 77, 77, 1)',
                    backgroundColor: 'rgba(255, 77, 77, 1)',
                    fill: false,
                },
            ],
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: {
                title:{
                    display: true,
                    text: 'TRANSACTIONS PER CATEGORY',
                    font: {
                        size: 20,
                    },
                    padding: {
                        bottom: 20,
                    },
                },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                },
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                    }
                },
                y: {
                    ticks: {
                        stepSize: 1,
                    },
                    pointLabels: {
                        fontSize: 12,
                    },
                },
            },
        },
    });
};

const computedClass = computed (() => {

    return (userStore.user_type == 'A' ? 'col-md-6 ml-4 chart2' : 'col-md-12 chart2') ;
})


onMounted(async () => {
    await loadTransactions()
    await loadCategories()
    await loadPaymentTypes()
    await loadVcardsActive()
    await loadTransactionsNotDeleted()
    await loadDistributionOfUsers()
    await loadTransactionsPerMonth()
    await loadTransactionsPerType()
    if (flag) {
        createChartLine()
        createChartBarHorizontal()
    }else{
        createChartVertical()
        createChartLineTransactions()
        createChartLineTransactionsType()
    }
    createChartPie()
});
</script>

<template>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard</h1>
    </div>
    <div class="container-fluid">
        <div class="row mt-5" v-if="numberOfTransactions != 0">
            <div class="col-md-4 d-flex justify-content-center mb-2">
                <div class="card text-white bg-success" style="width: 18rem;">
                    <div class="card-body text-center align-items-center d-flex justify-content-center">
                        <h5 v-if="flag" class="card-title">Vcard Balance: {{ balance }} €</h5>
                        <h5 v-else class="card-title">Active Vcards: {{ activeVcards }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4 d-flex justify-content-center mb-2">
                <div class="card text-white bg-warning" style="width: 18rem;">
                    <div class="card-body text-center align-items-center d-flex justify-content-center">
                        <h5 v-if="flag" class="card-title">Transactions: {{ numberOfTransactions }}</h5>
                        <h5 v-else class="card-title">Active Vcards Balance: {{ vcardsBalances }}€</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4 d-flex justify-content-center mb-2">
                <div class="card text-white bg-danger" style="width: 18rem;">
                    <div class="card-body text-center align-items-center d-flex justify-content-center">
                        <h5 v-if="flag" class="card-title">Used Categories: {{ numberOfCategories }}</h5>
                        <h5 v-else class="card-title">Transactions: {{ numberofAllTransactions }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="row d-flex justify-content-center mt-5" v-if="numberOfTransactions != 0">
            <div class="col-md-6 ml-4">
                <canvas v-if="flag" id="myChartLineTransactions"></canvas>
                <canvas v-else id="myChartLineTransactionsMonth"></canvas>
            </div>
            <div class="col-md-6 ml-4">
                <canvas  v-if="flag" id="myChartBarHorizontalTransactionsCategories"></canvas>
                <canvas v-else id="myChartBarVerticalUsers"></canvas>
            </div>
        </div>
    
        <div class="row d-flex justify-content-center mt-5" v-if="!flag && numberOfTransactions != 0">
            <div class="col-md-6 ml-4 chart2" >
                <canvas id="myChartPie"></canvas>
            </div>
            <div class="col-md-6 ml-4 chart2">
                <canvas id="myChartPolarAreaTransactionType"></canvas>
            </div>
        </div>
        <div class="row d-flex justify-content-center mt-5" v-if="flag && numberOfTransactions != 0">
            <div class="col-md-12 ml-4 chart2" >
                <canvas id="myChartPie"></canvas>
            </div>
        </div>
        <div class="col-md-12 d-flex justify-content-center mt-5" v-if="numberOfTransactions == 0">
            <h2>No Data</h2>
        </div>
    </div>
</template>

<style scoped>
.chart2 {
    width: 40%;
    height: auto;
    margin: 30px;
}
</style>