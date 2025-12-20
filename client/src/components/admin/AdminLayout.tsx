import { useEffect, useState } from "react";
import { Link, useLocation } from "wouter";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { checkAdminAuth, adminLogout, type AdminUser } from "@/lib/adminApi";
import { Button } from "@/components/ui/button";
import { 
  LayoutDashboard, 
  Package, 
  Mail, 
  LogOut, 
  Menu, 
  X,
  Settings
} from "lucide-react";

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  const [, setLocation] = useLocation();
  const [location] = useLocation();
  const queryClient = useQueryClient();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  const { data: authData, isLoading } = useQuery({
    queryKey: ['admin-auth'],
    queryFn: checkAdminAuth,
    retry: false,
  });

  useEffect(() => {
    if (!isLoading && !authData) {
      setLocation('/admin/login');
    }
  }, [authData, isLoading, setLocation]);

  const handleLogout = async () => {
    try {
      await adminLogout();
      queryClient.clear();
      setLocation('/admin/login');
    } catch (error) {
      console.error('Logout failed:', error);
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  if (!authData) {
    return null;
  }

  const admin = authData.admin;
  const navItems = [
    { href: "/admin/dashboard", label: "Dashboard", icon: <LayoutDashboard className="w-5 h-5" /> },
    { href: "/admin/packages", label: "Packages", icon: <Package className="w-5 h-5" /> },
    { href: "/admin/inquiries", label: "Inquiries", icon: <Mail className="w-5 h-5" /> },
  ];

  return (
    <div className="min-h-screen bg-muted/30">
      {/* Top Bar */}
      <header className="bg-white border-b border-border sticky top-0 z-40">
        <div className="flex items-center justify-between px-4 h-16">
          <div className="flex items-center gap-4">
            <button 
              onClick={() => setSidebarOpen(!sidebarOpen)}
              className="lg:hidden p-2"
            >
              {sidebarOpen ? <X /> : <Menu />}
            </button>
            <Link href="/admin/dashboard">
              <a className="text-xl font-heading font-bold text-primary">
                SINATH <span className="text-secondary">ADMIN</span>
              </a>
            </Link>
          </div>

          <div className="flex items-center gap-4">
            <div className="hidden md:block text-sm">
              <p className="font-medium">{admin.full_name}</p>
              <p className="text-xs text-muted-foreground">{admin.role}</p>
            </div>
            <Button 
              variant="ghost" 
              size="sm" 
              onClick={handleLogout}
              className="text-destructive hover:text-destructive"
            >
              <LogOut className="w-4 h-4 mr-2" />
              Logout
            </Button>
          </div>
        </div>
      </header>

      <div className="flex">
        {/* Sidebar */}
        <aside className={`
          fixed lg:sticky top-16 left-0 h-[calc(100vh-4rem)] 
          w-64 bg-white border-r border-border transition-transform duration-300 z-30
          ${sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'}
        `}>
          <nav className="p-4 space-y-2">
            {navItems.map((item) => (
              <Link key={item.href} href={item.href}>
                <a className={`
                  flex items-center gap-3 px-4 py-3 rounded-lg transition-colors
                  ${location === item.href 
                    ? 'bg-primary text-white' 
                    : 'text-foreground hover:bg-muted'
                  }
                `}>
                  {item.icon}
                  <span className="font-medium">{item.label}</span>
                </a>
              </Link>
            ))}
          </nav>
        </aside>

        {/* Main Content */}
        <main className="flex-1 p-6 lg:p-8">
          {children}
        </main>
      </div>

      {/* Overlay for mobile */}
      {sidebarOpen && (
        <div 
          className="fixed inset-0 bg-black/50 z-20 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}
    </div>
  );
}