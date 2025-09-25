<script setup>
  const props = defineProps({
    vcards: {
      type: Array,
      default: () => [],
    },
    showPhoneNumber: {
      type: Boolean,
      default: true,
    },
    showBlocked: {
      type: Boolean,
      default: true,
    },
    showBalance: {
      type: Boolean,
      default: false,
    },
    showMaxDebit: {
      type: Boolean,
      default: true,
    },
    showCreatedAt: {
      type: Boolean,
      default: false,
    },
    showUpdatedAt: {
      type: Boolean,
      default: true,
    },
    showEditButton: {
      type: Boolean,
      default: true,
    },
    showDeleteButton: {
      type: Boolean,
      default: true,
    }
  })

  const emit = defineEmits(['edit', 'delete', 'block'])

  const editClick = (vcard) => {
      emit('edit', vcard)
  }

  const deleteClick = (vcard) => {
      emit('delete', vcard)
  }

  const blockClick = (vcard) => {
    emit('block', vcard)
  }

</script>

<template>
  <table class="table">
    <thead>
      <tr>
        <th v-if="showPhoneNumber">Phone Number</th>
        <th>Name</th>
        <th>Email</th>
        <th v-if="showBlocked">Blocked</th>
        <th v-if="showBalance">Balance</th>
        <th v-if="showMaxDebit">Max Debit</th>
        <th v-if="showCreatedAt">Created At</th>
        <th v-if="showEditButton || showDeleteButton"></th>
      </tr>
    </thead>
    <tbody>
      <tr
        v-for="vcard in vcards"
        :key="vcard.id"
      >
        <td v-if="showPhoneNumber">{{ vcard.phone_number }}</td>
        <td>{{ vcard.name }}</td>
        <td>{{ vcard.email }}</td>
        <td v-if="showBlocked">{{ vcard.blocked ? 'Blocked' : 'Not Blocked' }}</td>
        <td v-if="showBalance">{{ vcard.balance }}</td>
        <td v-if="showMaxDebit">{{ vcard.max_debit }} €</td>
        <td v-if="showCreatedAt">{{ vcard.created_at }}</td>
        <td
          class="text-end"
          v-if="showEditButton || showDeleteButton"
        >
          <div class="d-flex justify-content-end">
            <button
              class="btn btn-xs btn-light"
              @click="editClick(vcard)"
              v-if="showEditButton"
            ><i class="bi bi-xs bi-pencil"></i>
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="deleteClick(vcard)"
              v-if="showDeleteButton"
            ><i class="bi bi-xs bi-x-square-fill"></i>
            </button>
            <button
              class="btn btn-xs btn-light"
              @click="blockClick(vcard)"
              v-if="showDeleteButton"
            ><i class="bi bi-xs bi-ban"></i>
            </button>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<style scoped>
button {
  margin-left: 3px;
  margin-right: 3px;
}
</style>
