<template>
  <div class="container d-flex align-items-center justify-content-center min-vh-100 bg-light">
    <div class="card shadow-lg p-4 w-100">
      <div class="card-body">
        <h3 class="card-title text-center mb-4">Create Account</h3>
        <form @submit.prevent="register">
          <div class="mb-3">
            <label  for="name" class="form-label">Full Name</label>
            <input
              v-model="data.name"
              type="text"
              class="form-control"
              id="name"
              placeholder="Enter your name"
              required
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
            />
          </div>
          <button type="submit" class="btn btn-primary w-100 mb-3">Sign Up</button>
          <div class="text-center">
            <router-link :to="{ name:'login'}" class="text-decoration-none">Already have an account? Sign In</router-link>
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
  data(){
    return{
      data: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
      },
      error: null
    }
  },
  methods: {
    async register() {
      try {
        await this.$store.dispatch('register', this.form);
        this.$router.push('/chat');
      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed';
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
  transition: background-color 0.3s;
}
.btn-primary:hover {
  background-color: #0056b3;
}
</style>
