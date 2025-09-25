<script setup>
  const props = defineProps({
    categories: {
      type: Array,
      default: () => [],
    },
    showOwner: {
      type: Boolean,
      default: true
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

  const emit = defineEmits(['edit', 'delete'])

  const editClick = (category) => {
      emit('edit', category)
  }

  const deleteClick = (category) => {
      emit('delete', category)
  }

</script>

<template>
    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Type</th>
          <th v-if="showOwner">Owner</th>
          <th v-if="showEditButton || showDeleteButton"></th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="category in categories"
          :key="category.id"
        >
          <td>{{ category.name }}</td>
          <td>{{ category.type == 'C' ? 'Credit' : 'Debit'}}</td>
          <td v-if="showOwner">{{ category.vcard?.name }}</td>
          <td
          class="text-end"
          v-if="showEditButton || showDeleteButton"
        >
          <div class="d-flex justify-content-end">
            <button
              class="btn btn-xs btn-light"
              @click="editClick(category)"
              v-if="showEditButton"
            ><i class="bi bi-xs bi-pencil"></i>
            </button>

            <button
              class="btn btn-xs btn-light"
              @click="deleteClick(category)"
              v-if="showDeleteButton"
            ><i class="bi bi-xs bi-x-square-fill"></i>
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
  