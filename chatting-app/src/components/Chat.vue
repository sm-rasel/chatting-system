<template>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-4 col-lg-3 p-0 bg-white sidebar" :class="{ 'active': sidebarVisible }">
        <div class="p-3 bg-whatsapp-green text-white d-flex justify-content-between align-items-center">
          <h4 class="mb-0">Chats</h4>
          <button class="btn btn-outline-light d-md-none" @click="toggleSidebar">
            <i class="bi bi-list"></i>
          </button>
        </div>
        <div class="p-3">
          <input
            v-model="searchQuery"
            type="text"
            class="form-control rounded-pill"
            placeholder="Search chats..."
            @input="searchUsers"
          />
          <ul v-if="searchResults.length" class="list-group mt-3">
            <li
              v-for="user in searchResults"
              :key="user.id"
              class="list-group-item border-0 py-2"
              @click="startConversation(user.id)"
            >
              <div class="d-flex align-items-center">
                <div class="avatar me-2">
                  <i class="bi bi-person-circle"></i>
                </div>
                <span>{{ user.name }}</span>
              </div>
            </li>
          </ul>
        </div>
        <ul class="list-group list-group-flush">
          <template v-if="conversations.length > 0">
            <li
              v-for="conversation in conversations"
              :key="conversation.id"
              class="list-group-item border-0 py-3"
              :class="{ active: activeConversation?.id === conversation.id }"
              @click="selectConversation(conversation)"
            >
              <div class="d-flex align-items-center">
                <div class="avatar me-2">
                  <i class="bi bi-person-circle"></i>
                </div>
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between">
                    <span class="fw-bold">{{ getConversationName(conversation) }}</span>
                    <small class="text-muted">{{ conversation.last_message ? formatTime(conversation.last_message.created_at) : '' }}</small>
                  </div>
                  <small class="text-muted d-block text-truncate" style="max-width: 200px;">
                    {{ conversation.last_message?.message || 'No messages yet' }}
                  </small>
                </div>
              </div>
            </li>
          </template>
        </ul>
        <button class="btn btn-outline-secondary m-3 rounded-pill" @click="logout">Logout</button>
      </div>

      <!-- Chat Area -->
      <div class="col-md-8 col-lg-9 p-0" style="margin-left: 431px;">
        <div v-if="activeConversation" class="chat-area">
          <div class="chat-header p-3 bg-whatsapp-green text-white d-flex align-items-center">
            <button class="btn btn-outline-light me-2 d-md-none" @click="activeConversation = null">
              <i class="bi bi-arrow-left"></i>
            </button>
            <div class="avatar me-2">
              <i class="bi bi-person-circle"></i>
            </div>
            <div class="flex-grow-1">
              <h5 class="mb-0">{{ getOtherUserName() }}</h5>
              <small class="text-light">Online</small>
            </div>
          </div>
          <div class="chat-body p-3" ref="chatBody" style="background-image: url('https://i.imgur.com/avRBRpN.jpg');">
            <div v-if="loadingMessages" class="text-center my-3">
              <div class="spinner-border text-whatsapp-green" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <div v-else>
              <div v-for="message in messages" :key="message.id" class="message mb-2" :class="{ 'text-end': message.sender.id === user.id }">
                <div class="message-bubble p-2 rounded-3 shadow-sm" :class="{ 'bg-whatsapp-sent': message.sender.id === user.id, 'bg-whatsapp-received': message.sender.id !== user.id }">
                  {{ message.message }}
                  <small class="d-block text-muted mt-1" :class="{ 'text-end': message.sender.id === user.id }">
                    {{ formatTime(message.created_at) }}
                  </small>
                </div>
              </div>
            </div>
          </div>
          <div class="chat-footer p-3 bg-white">
            <form @submit.prevent="sendMessage">
              <div class="input-group">
                <input
                  v-model="newMessage"
                  type="text"
                  class="form-control rounded-pill"
                  placeholder="Type a message..."
                  :disabled="sendingMessage"
                  required
                />
                <button type="submit" class="btn btn-whatsapp-green rounded-pill ms-2" :disabled="sendingMessage">
                  <span v-if="sendingMessage" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                  <i v-else class="bi bi-send"></i>
                </button>
              </div>
            </form>
          </div>
        </div>
        <div v-else class="d-flex justify-content-center align-items-center h-100 bg-light">
          <div class="text-center">
            <i class="bi bi-chat-square-text" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-2">Select a chat to start messaging</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../../plugins/axios.js';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { format, parseISO } from 'date-fns';

export default {
  data() {
    return {
      user: null,
      searchQuery: '',
      searchResults: [],
      conversations: [],
      activeConversation: null,
      messages: [],
      newMessage: '',
      sidebarVisible: true,
      loadingMessages: false,
      sendingMessage: false,
      echo: null,
    };
  },

  async created() {
    await this.fetchUser();
    if (this.user) {
      await this.fetchConversations();
      this.setupGlobalWebSocket();
    } else {
      console.error('User not loaded, skipping WebSocket and conversations setup');
    }
  },

  beforeUnmount() {
    if (this.echo) {
      this.echo.disconnect();
      console.log('WebSocket disconnected');
    }
  },

  methods: {
    toggleSidebar() {
      this.sidebarVisible = !this.sidebarVisible;
    },

    async fetchUser() {
      try {
        const response = await axios.get('/user');
        this.user = response.data;
        console.log('User fetched:', this.user);
      } catch (error) {
        console.error('Error fetching user:', error);
      }
    },

    async searchUsers() {
      if (this.searchQuery.trim() === '') {
        this.searchResults = [];
        return;
      }

      try {
        const response = await axios.get('/search', {
          params: { q: this.searchQuery },
        });
        this.searchResults = response.data.filter(user => user.id !== this.user.id);
      } catch (error) {
        console.error('Error searching users:', error);
        this.searchResults = [];
      }
    },

    async startConversation(userId) {
      try {
        const response = await axios.post('/conversations', { user_id: userId });
        this.conversations.push(response.data);
        this.searchResults = [];
        this.searchQuery = '';
        await this.selectConversation(response.data);
      } catch (error) {
        console.error('Error starting conversation:', error);
      }
    },

    async fetchConversations() {
      try {
        const response = await axios.get('/conversations');
        this.conversations = response.data.map(conv => ({
          ...conv,
          last_message: conv.messages?.[0] || null
        }));
        console.log('Conversations fetched:', this.conversations);
      } catch (error) {
        console.error('Error fetching conversations:', error);
      }
    },

    async selectConversation(conversation) {
      // Leave the previous conversation channel if it exists
      if (this.activeConversation && this.echo) {
        this.echo.leave(`conversation.${this.activeConversation.id}`);
        console.log(`Left channel: conversation.${this.activeConversation.id}`);
      }

      this.activeConversation = conversation;
      this.loadingMessages = true;

      try {
        const response = await axios.get(`/conversations/${conversation.id}/messages`);
        this.messages = response.data.data;
        this.$nextTick(() => {
          this.scrollToBottom();
        });

        // Set up WebSocket listener for the new conversation
        if (this.echo && this.user) {
          this.echo.private(`conversation.${conversation.id}`)
            .listen('MessageSent', (data) => {
              if (data.chat.sender.id !== this.user.id) {
                this.messages.push(data.chat);
                this.$nextTick(() => {
                  this.scrollToBottom();
                });

                // Update last message in conversations list
                const convIndex = this.conversations.findIndex(c => c.id === data.chat.conversation_id);
                if (convIndex !== -1) {
                  this.conversations[convIndex].last_message = data.chat;
                }
              }
              console.log('Received message:', data.chat);
            })
            .error((error) => {
              console.error('WebSocket error on conversation channel:', error);
            });
          console.log(`Subscribed to channel: conversation.${conversation.id}`);
        } else {
          console.warn('WebSocket not initialized or user not available');
        }
      } catch (error) {
        console.error('Error fetching messages:', error);
      } finally {
        this.loadingMessages = false;
      }
    },

    async sendMessage() {
      if (!this.newMessage.trim() || !this.activeConversation) return;

      this.sendingMessage = true;
      const messageContent = this.newMessage;
      this.newMessage = '';

      try {
        const tempId = Date.now();
        const tempMessage = {
          id: tempId,
          message: messageContent,
          sender: this.user,
          receiver: this.getOtherUser(),
          created_at: new Date().toISOString(),
          is_temp: true
        };

        this.messages.push(tempMessage);
        this.$nextTick(() => {
          this.scrollToBottom();
        });

        const response = await axios.post(
          `/conversations/${this.activeConversation.id}/messages`,
          { message: messageContent }
        );

        // Replace temp message with real message
        const index = this.messages.findIndex(m => m.id === tempId);
        if (index !== -1) {
          this.messages.splice(index, 1, response.data);
        }

        // Update last message in conversations list
        const convIndex = this.conversations.findIndex(c => c.id === this.activeConversation.id);
        if (convIndex !== -1) {
          this.conversations[convIndex].last_message = response.data;
        }
      } catch (error) {
        console.error('Error sending message:', error);
        this.newMessage = messageContent; // Restore message if failed
      } finally {
        this.sendingMessage = false;
      }
    },

    getConversationName(conversation) {
      if (!conversation || !this.user) return '';
      return conversation.user_one_id === this.user.id
        ? conversation.user_two?.name
        : conversation.user_one?.name;
    },

    getOtherUserName() {
      if (!this.activeConversation || !this.user) return '';
      return this.activeConversation.user_one_id === this.user.id
        ? this.activeConversation.user_two?.name
        : this.activeConversation.user_one?.name;
    },

    getOtherUser() {
      if (!this.activeConversation || !this.user) return null;
      return this.activeConversation.user_one_id === this.user.id
        ? this.activeConversation.user_two
        : this.activeConversation.user_one;
    },

    formatDate(dateString) {
      if (!dateString) return '';
      return format(parseISO(dateString), 'MMM d, yyyy');
    },

    formatTime(dateString) {
      if (!dateString) return '';
      return format(parseISO(dateString), 'h:mm a');
    },

    scrollToBottom() {
      const chatBody = this.$refs.chatBody;
      if (chatBody) {
        chatBody.scrollTop = chatBody.scrollHeight;
      }
    },

    setupGlobalWebSocket() {
      if (!this.user) {
        console.warn('User not loaded, skipping WebSocket setup');
        return;
      }

      window.Pusher = Pusher;

      this.echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT,
        wssPort: import.meta.env.VITE_REVERB_PORT,
        forceTLS: false,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: `${import.meta.env.VITE_API_URL}/broadcasting/auth`,
        auth: {
          headers: {
            Authorization: `Bearer ${this.$store.state.token}`,
          },
        },
      });

      // Listen for connection events
      this.echo.connector.pusher.connection.bind('connected', () => {
        console.log('WebSocket connected');
      });

      this.echo.connector.pusher.connection.bind('disconnected', () => {
        console.warn('WebSocket disconnected');
      });

      this.echo.connector.pusher.connection.bind('error', (error) => {
        console.error('WebSocket connection error:', error);
      });

      // Listen for new conversations
      this.echo.private(`user.${this.user.id}`)
        .listen('ConversationCreated', (data) => {
          this.conversations.unshift(data.conversation);
          console.log('New conversation created:', data.conversation);
        })
        .error((error) => {
          console.error('WebSocket error on user channel:', error);
        });

      console.log('WebSocket initialized for user:', this.user.id);
    },

    async logout() {
      try {
        await this.$store.dispatch('logout');
        this.$router.push('/login');
      } catch (error) {
        console.error('Error logging out:', error);
      }
    },
  },
};
</script>

<style scoped>
.bg-whatsapp-green {
  background-color: #128C7E;
}

.bg-whatsapp-sent {
  background-color: #DCF8C6;
  color: #000;
}

.bg-whatsapp-received {
  background-color: #FFFFFF;
  color: #000;
}

.sidebar {
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  z-index: 100;
  overflow-y: auto;
  transition: transform 0.3s ease;
  border-right: 1px solid #e9ecef;
}

.chat-area {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.chat-body {
  flex: 1;
  overflow-y: auto;
  background-size: cover;
  background-position: center;
}

.message-bubble {
  max-width: 70%;
  display: inline-block;
  word-wrap: break-word;
  position: relative;
}

.message-bubble::after {
  content: '';
  position: absolute;
  bottom: 0;
  width: 10px;
  height: 10px;
}

.message-bubble.bg-whatsapp-sent {
  border-bottom-right-radius: 0;
}

.message-bubble.bg-whatsapp-sent::after {
  right: -10px;
  border-bottom-left-radius: 10px;
  background: #DCF8C6;
}

.message-bubble.bg-whatsapp-received {
  border-bottom-left-radius: 0;
}

.message-bubble.bg-whatsapp-received::after {
  left: -10px;
  border-bottom-right-radius: 10px;
  background: #FFFFFF;
}

.list-group-item.active {
  background-color: #e9ecef;
  color: #000;
}

.avatar {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #999;
}

.form-control:focus {
  border-color: #128C7E;
  box-shadow: 0 0 5px rgba(18, 140, 126, 0.5);
}

.btn-whatsapp-green {
  background-color: #128C7E;
  color: #fff;
}

.btn-whatsapp-green:hover {
  background-color: #0e6d62;
}

@media (max-width: 767px) {
  .sidebar {
    width: 100%;
    transform: translateX(-100%);
  }
  .sidebar.active {
    transform: translateX(0);
  }
  .col-md-8 {
    position: absolute;
    width: 100%;
  }
}
</style>
