<template>
  <div class="container d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="card shadow-lg p-4 w-100" style="max-width: 500px;">
      <div class="card-body">
        <h3 class="card-title text-center mb-4">Create Account</h3>
        <form @submit.prevent="register">
          <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input
              v-model="data.name"
              type="text"
              class="form-control"
              id="name"
              placeholder="Enter your name"
              required
              :disabled="isLoading"
            />
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input
              v-model="data.email"
              type="email"
              class="form-control"
              id="email"
              placeholder="Enter your email"
              required
              :class="{ 'is-invalid': error && error.includes('email') }"
              :disabled="isLoading"
            />
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
              v-model="data.password"
              type="password"
              class="form-control"
              id="password"
              placeholder="Create a password"
              required
              :class="{ 'is-invalid': error && error.includes('password') }"
              :disabled="isLoading"
            />
          </div>
          <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input
              v-model="data.password_confirmation"
              type="password"
              class="form-control"
              id="password_confirmation"
              placeholder="Confirm your password"
              required
              :class="{ 'is-invalid': error && error.includes('password') }"
              :disabled="isLoading"
            />
          </div>
          <button
            type="submit"
            class="btn btn-primary w-100 mb-3 position-relative"
            :disabled="isLoading"
            :class="{ 'btn-loading': isLoading }"
          >
            <span :class="{ 'invisible': isLoading }">Sign Up</span>
            <span v-if="isLoading" class="loading-spinner"></span>
          </button>
          <div class="text-center">
            <router-link
              :to="{ name:'login'}"
              class="text-decoration-none"
              :class="{ 'pe-none': isLoading }"
            >
              Already have an account? Sign In
            </router-link>
          </div>
          <div v-if="error" class="alert alert-danger mt-3" role="alert">{{ error }}</div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Register",
  data() {
    return {
      data: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
      },
      error: null,
      isLoading: false
    }
  },
  methods: {
    async register() {
      this.error = null;
      this.isLoading = true;

      try {
        await this.$store.dispatch('register', this.data);
        this.$router.push('/chat');
      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed';
      } finally {
        this.isLoading = false;
      }
    },
  }
}
</script>

<style scoped>
.card {
  border: none;
  border-radius: 10px;
}

.btn-primary {
  background-color: #007bff;
  border-color: #007bff;
  transition: all 0.3s ease;
  height: 45px;
  position: relative;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-primary:active:not(:disabled) {
  transform: translateY(0);
}

.btn-primary:disabled {
  opacity: 0.8;
  cursor: not-allowed;
}

.btn-loading {
  background-color: #0056b3 !important;
}

.loading-spinner {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Disable pointer events during loading */
.pe-none {
  pointer-events: none;
  opacity: 0.7;
}
</style>
