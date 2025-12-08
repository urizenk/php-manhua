            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // 子菜单展开/收起
            $('.submenu-toggle').click(function() {
                $(this).parent('.has-submenu').toggleClass('open');
                $(this).next('.submenu').slideToggle(200);
            });
            
            // 高亮当前页面菜单
            var currentPath = window.location.pathname;
            $('.sidebar-menu a').each(function() {
                if ($(this).attr('href') === currentPath) {
                    $(this).addClass('active');
                    $(this).parents('.has-submenu').addClass('open');
                    $(this).parents('.submenu').show();
                }
            });
            
            // 移动端菜单切换
            $('#mobileMenuToggle').click(function() {
                $('#sidebar').toggleClass('show');
                $('#sidebarOverlay').toggleClass('show');
            });
            
            // 点击遮罩层关闭菜单
            $('#sidebarOverlay').click(function() {
                $('#sidebar').removeClass('show');
                $('#sidebarOverlay').removeClass('show');
            });
            
            // 点击菜单项后关闭菜单（移动端）
            $('.sidebar-menu a').click(function() {
                if (window.innerWidth <= 768) {
                    $('#sidebar').removeClass('show');
                    $('#sidebarOverlay').removeClass('show');
                }
            });
        });
    </script>
    <?php echo $customJs ?? ''; ?>
</body>
</html>


