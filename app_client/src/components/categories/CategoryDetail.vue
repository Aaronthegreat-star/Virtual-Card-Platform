<script setup>
  import { ref, watch, computed } from 'vue'

  const props = defineProps({
    category: {
      type: Object,
      required: true
    },
    operationType: {
      type: String,
      default: 'insert'  // insert / update
    },
    errors: {
      type: Object,
      required: false,
    },
  })

  const emit = defineEmits(['save', 'cancel'])

  const editingCategory = ref(props.category)

  watch(
    () => props.category, 
    (newcategory) => { 
        editingCategory.value = newcategory
    }
  )

  const save = () => {
      emit('save', editingCategory.value)
  }

  const cancel = () => {
    emit('cancel', editingCategory.value)
  }

</script>

<template>
    <form
      class="row g-3 needs-validation"
      novalidate
      @submit.prevent="save"
    >
      <h3 class="mt-5 mb-3"></h3>
      <hr>
  
      <div class="d-flex flex-wrap justify-content-between">
        <div class="mb-3 me-3 flex-grow-1">
          <label
            for="inputName"
            class="form-label"
          >Name *</label>
          <input
            type="text"
            class="form-control"
            id="inputName"
            placeholder="Category Name"
            required
            v-model="editingCategory.name"
          >
          <field-error-message :errors="errors" fieldName="name"></field-error-message>
        </div>
        <div class="mb-3 me-3 flex-grow-1">
            <label for="selectType" class="form-label">Type: *</label>
            <select class="form-select" id="selectType" v-model="editingCategory.type" required>
                <option value="D">Debit</option>
                <option value="C">Credit</option>
            </select>
            <field-error-message :errors="errors" fieldName="type"></field-error-message>
        </div>
    </div>
  
      <div class="d-flex flex-wrap justify-content-between">
      </div>
  
      <div class="mb-3 d-flex justify-content-center ">
        <button
          type="button"
          class="btn btn-success px-5 mx-2"
          @click="save"
        >{{ props.operationType == 'update' ? 'Edit' : 'Create'}}</button>
        <button
          type="button"
          class="btn btn-dark px-5"
          @click="cancel"
        >Cancel</button>
      </div>
  
    </form>
  </template>
  
  <style scoped>
  .total_price {
    width: 26rem;
  }
  .checkBilled {
    margin-top: 0.4rem;
    min-height: 2.375rem;
  }
  </style>
  