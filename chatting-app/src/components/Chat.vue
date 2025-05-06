<template>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-2 p-0 bg-dark text-white sidebar">
        <div class="p-3 d-flex justify-content-between align-items-center">
          <h4>Chats</h4>
          <button class="btn btn-outline-light d-md-none" @click="toggleSidebar">☰</button>
        </div>
        <div class="mb-3 px-3">

          <input @input="handleInput" type="text" class="form-control" placeholder="Search users..." />
          <ul v-if="searchResults?.length" class="list-group mt-2">
            <li v-for="user in searchResults" :key="user.id" class="list-group-item list-group-item-dark" @click="startConversation(user.id)">
              {{ user.name }}
            </li>
          </ul>
        </div>
        <ul class="list-group">
          <li v-for="conversation in conversations" :key="conversation.id" class="list-group-item list-group-item-dark" :class="{ active: activeConversation?.id === conversation.id }" @click="selectConversation(conversation)">
            {{ conversation.user_one_id === user.id ? conversation.user_two_id?.name : conversation.user_one_id?.name }}
            <small class="text-muted">{{ conversation.messages[0]?.message }}</small>
          </li>
        </ul>
        <button class="btn btn-outline-light m-3" @click="logout">Logout</button>
      </div>
      <!-- Chat Area -->
      <div class="col-md-9 col-lg-10 p-0">
        <div v-if="activeConversation" class="chat-area">
          <div class="chat-header p-3 bg-primary text-white">
            <h5>{{ activeConversation.user_one_id.id === user.id ? activeConversation.user_two_id.name : activeConversation.user_one_id.name }}</h5>
          </div>
          <div class="chat-body p-3" ref="chatBody">
            <div v-for="message in messages" :key="message.id" class="message mb-3" :class="{ 'text-end': message.sender.id === user.id }">
              <div class="message-bubble p-2 rounded" :class="{ 'bg-primary text-white': message.sender.id === user.id, 'bg-light': message.sender.id !== user.id }">
                {{ message.message }}
                <small class="d-block text-muted">{{ message.created_at }}</small>
              </div>
            </div>
          </div>
          <div class="chat-footer p-3">
            <form @submit.prevent="sendMessage">
              <div class="input-group">
                <input v-model="newMessage" type="text" class="form-control" placeholder="Type a message..." required />
                <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i></button>
              </div>
            </form>
          </div>
        </div>
        <div v-else class="d-flex justify-content-center align-items-center h-100">
          <p class="text-muted">Select a conversation to start chatting</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../plugins/axios.js';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import {ServiceBaseUrl} from "../../config/api_config.js";

export default {
  data() {
    return {
      user: this.$store.state.user,
      searchQuery: '',
      searchResults: [],
      conversations: [],
      activeConversation: null,
      messages: [],
      newMessage: '',
      sidebarVisible: true,
    };
  },
  watch: {
    activeConversation(newConversation) {
      if (newConversation) {
        this.setupWebSocket();
      }
    },
  },
  methods: {
    toggleSidebar() {
      this.sidebarVisible = !this.sidebarVisible;
    },
    handleInput(event) {
      this.searchQuery = event.target.value;
      this.searchUsers();
    },
     searchUsers() {
      if (this.searchQuery.trim() === '') {
        this.searchResults = [];
        return;
      }
       axios.get('/search', {
        params: { q: this.searchQuery },
      }).then((res) => {
        this.searchResults = res.data;
       });

    },
    async startConversation(userId) {
      const response = await axios.post('/conversations', { user_id: userId });

      this.conversations.push(response.data);
      console.log('conversations =>', this.conversations);
      this.searchResults = [];
      this.searchQuery = '';
      this.selectConversation(response.data);
    },
    async fetchConversations() {
      const response = await axios.get('/conversations');
      this.conversations = response.data;
    },
    async selectConversation(conversation) {
      this.activeConversation = conversation;
      const response = await axios.get('/conversations/${conversation.id}/messages');
      this.messages = response.data.data.reverse();
      this.$nextTick(() => {
        this.scrollToBottom();
      });
    },
    async sendMessage() {
      if (!this.newMessage.trim()) return;
      const response = await axios.post('/conversations/${this.activeConversation.id}/messages', {
        message: this.newMessage,
      });
      this.messages.push(response.data);
      this.newMessage = '';
      this.$nextTick(() => {
        this.scrollToBottom();
      });
    },
    async logout() {
      await this.$store.dispatch('logout');
      this.$router.push('/login');
    },
    setupWebSocket() {
      window.Pusher = Pusher;
      Pusher.logToConsole = true;
      this.echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT,
        wssPort: import.meta.env.VITE_REVERB_PORT,
        forceTLS: false,
        enabledTransports: ['ws'],
        authEndpoint: `${import.meta.env.VITE_API_URL}/broadcasting/auth`,
        auth: {
          headers: {
            Authorization: `Bearer ${this.$store.state.token}`,
          },
        },
      });
      if (this.activeConversation) {
        this.echo.channel(`conversation.${this.activeConversation.id}`).listen('MessageSent', (e) => {
          this.messages.push(e.chat);
          this.$nextTick(() => {
            this.scrollToBottom();
          });
        });
      }
    },
    scrollToBottom() {
      const chatBody = this.$refs.chatBody;
      chatBody.scrollTop = chatBody.scrollHeight;
    },
  },
  created() {
    this.fetchConversations();
    this.setupWebSocket();

    // console.log('axios =>', axios.defaults.baseURL);
  },
};
</script>

<style scoped>
.sidebar {
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  z-index: 100;
  overflow-y: auto;
}
.chat-area {
  height: 100vh;
  display: flex;
  flex-direction: column;
}
.chat-body {
  flex: 1;
  overflow-y: auto;
}
.message-bubble {
  max-width: 70%;
  display: inline-block;
}
@media (max-width: 767px) {
  .sidebar {
    width: 250px;
    transform: translateX(-250px);
    transition: transform 0.3s ease;
  }
  .sidebar.active {
    transform: translateX(0);
  }
}
</style>
