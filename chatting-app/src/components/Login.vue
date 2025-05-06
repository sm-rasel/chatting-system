<template>
    <div class="login">
      <div class="container d-flex align-items-center justify-content-center min-vh-100 bg-light col-12">
        <div class="card shadow-lg p-4" style="max-width: 400px; width: 100%;">
          <div class="card-body">
            <h4 class="card-title text-center mb-4">Login</h4>
            <form @submit.prevent="login">
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                  v-model="data.email"
                  type="email"
                  class="form-control"
                  id="email"
                  placeholder="Enter your email"
                  required
                  :class="{ 'is-invalid': error && error.includes('email') }"
                />
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input
                  v-model="data.password"
                  type="password"
                  class="form-control"
                  id="password"
                  placeholder="Enter your password"
                  required
                  :class="{ 'is-invalid': error && error.includes('password') }"
                />
              </div>
              <button type="submit" class="btn btn-outline-primary w-100 mb-3">Sign In</button>
              <div class="text-center">
                <router-link :to="{ name: 'register' }" class="text-decoration-none">Don't have an account? Sign Up</router-link>
              </div>
            </form>
            <div v-if="error" class="alert alert-danger mt-3">{{ error }}</div>
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
      error: null
    }
  },
  methods: {
    async login() {
      try {
        await this.$store.dispatch('login', this.data)
        this.$router.push('/chat');
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed'
      }
    }
  }
}
</script>
<style scoped>
.card {
  border: none;
  border-radius: 10px;
}
</style>
