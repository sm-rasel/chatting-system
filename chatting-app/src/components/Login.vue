<template>
  <div class="login">
    <div class="container d-flex align-items-center justify-content-center min-vh-100 bg-light col-12">
      <div class="card shadow-lg p-4" style="max-width: 400px; width: 100%;">
        <div class="card-body">
          <div class="text-center mb-4">
            <h4 class="card-title mb-2">Welcome Back</h4>
            <p class="text-muted">Please enter your credentials to login</p>
          </div>
          <form @submit.prevent="login">
            <div class="mb-3">
              <label for="email" class="form-label">Email Address</label>
              <div class="input-group">
                <span class="input-group-text">
                  <i class="bi bi-envelope"></i>
                </span>
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
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <div class="input-group">
                <span class="input-group-text">
                  <i class="bi bi-lock"></i>
                </span>
                <input
                  v-model="data.password"
                  type="password"
                  class="form-control"
                  id="password"
                  placeholder="Enter your password"
                  required
                  :class="{ 'is-invalid': error && error.includes('password') }"
                  :disabled="isLoading"
                />
              </div>
<!--              <div class="text-end mt-2">-->
<!--                <router-link-->
<!--                  :to="{ name: 'forgot-password' }"-->
<!--                  class="text-decoration-none small"-->
<!--                  :class="{ 'pe-none': isLoading }"-->
<!--                >-->
<!--                  Forgot Password?-->
<!--                </router-link>-->
<!--              </div>-->
            </div>
            <button
              type="submit"
              class="btn btn-primary w-100 mb-3 py-2 position-relative"
              :disabled="isLoading"
              :class="{ 'btn-loading': isLoading }"
            >
              <span :class="{ 'invisible': isLoading }">Login</span>
              <span v-if="isLoading" class="loading-spinner"></span>
            </button>
            <div class="text-center">
              <router-link
                :to="{ name: 'register' }"
                class="text-decoration-none"
                :class="{ 'pe-none': isLoading, 'text-muted': isLoading }"
              >
                Don't have an account? <strong>Sign Up</strong>
              </router-link>
            </div>
<!--            <div class="divider d-flex align-items-center my-3">-->
<!--              <span class="text-muted small mx-2">OR</span>-->
<!--            </div>-->
<!--            <div class="d-grid gap-2">-->
<!--              <button-->
<!--                type="button"-->
<!--                class="btn btn-outline-secondary"-->
<!--                :disabled="isLoading"-->
<!--              >-->
<!--                <i class="bi bi-google me-2"></i> Continue with Google-->
<!--              </button>-->
<!--            </div>-->
          </form>
          <div v-if="error" class="alert alert-danger mt-3 mb-0">{{ error }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Login",
  data() {
    return {
      data: {
        email: '',
        password: '',
      },
      isLoading: false,
      error: null
    }
  },
  methods: {
    async login() {
      this.error = null;
      this.isLoading = true;
      try {
        await this.$store.dispatch('login', this.data)
        this.$router.push('/chat');
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed. Please check your credentials and try again.'
      } finally {
        this.isLoading = false;
      }
    }
  }
}
</script>

<style scoped>
.card {
  border: none;
  border-radius: 12px;
}

.btn-primary {
  background-color: #4361ee;
  border-color: #4361ee;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.btn-primary:hover:not(:disabled) {
  background-color: #3a56d4;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
}

.btn-primary:active:not(:disabled) {
  transform: translateY(0);
}

.btn-primary:disabled {
  opacity: 0.85;
  cursor: not-allowed;
}

.btn-loading {
  background-color: #3a56d4 !important;
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
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: translate(-50%, -50%) rotate(360deg); }
}

.pe-none {
  pointer-events: none;
}

.input-group-text {
  background-color: #f8f9fa;
}

.divider {
  color: #6c757d;
}

.divider::before,
.divider::after {
  content: "";
  flex: 1;
  border-bottom: 1px solid #dee2e6;
}

.divider::before {
  margin-right: 0.5rem;
}

.divider::after {
  margin-left: 0.5rem;
}

.alert {
  transition: all 0.3s ease;
}

/* Add some subtle animation to the card */
.card {
  animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
