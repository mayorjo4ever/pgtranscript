<div>
   
<style>
    .tab-container {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      max-width: 99%;
      margin: auto;
      overflow: hidden;
    }

    .tabs {
      display: flex;
      border-bottom: 1px solid #ddd;
      background: #fafafa;
    }

    .tab {
      flex: 1;
      text-align: center;
      padding: 12px 8px;
      cursor: pointer;
      transition: background 0.3s, color 0.3s;
      font-weight: 500;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .tab .material-icons {
      font-size: 20px;
      margin-bottom: 4px;
    }

    .tab:hover {
      background: #f0f0f0;
    }

    .tab.active {
      color: #FFF;
      background: #e91e63;
      border-bottom: 3px solid #FFF;
    }

    .tab-content {
      display: none;
      padding: 20px;
      animation: fade 0.5s ease-in-out;
    }

    .tab-content.active {
      display: block;
    }

    </style>
    
<script>
  const tabs = document.querySelectorAll('.tab');
  const contents = document.querySelectorAll('.tab-content');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      // Remove active classes
      tabs.forEach(t => t.classList.remove('active'));
      contents.forEach(c => c.classList.remove('active'));

      // Activate clicked tab and related content
      tab.classList.add('active');
      document.getElementById(tab.dataset.tab).classList.add('active');
    });
  });
</script>

</div>